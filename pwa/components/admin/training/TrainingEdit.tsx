import { useState, useEffect } from "react";
import {
  Edit,
  SimpleForm,
  SelectInput,
  ReferenceInput,
  AutocompleteInput,
  DateInput,
  FileInput,
  required,
  useRecordContext,
  useNotify,
  useRefresh,
} from "react-admin";
import { MyFileField } from "../shared/OdooDocumentField";
import { syncMediaDocuments } from "../../../app/lib/client";
import { isDefinedAndNotVoid } from "../../../app/lib/utils";
import {
  Box,
  Typography,
  Chip,
  LinearProgress,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  TextField,
  ToggleButtonGroup,
  ToggleButton,
  Tooltip,
  Divider,
} from "@mui/material";
import { useSessionContext } from "../../admin/SessionContextProvider";

const statutChoices = [
  { id: 'en_cours', name: 'En cours' },
  { id: 'termine', name: 'Terminé' },
  { id: 'abandonne', name: 'Abandonné' },
];

const niveauConfig = [
  { value: 0, label: 'Non abordé', short: '—', color: '#e0e0e0', textColor: '#666' },
  { value: 1, label: 'Présenté', short: 'P', color: '#ffcdd2', textColor: '#c62828' },
  { value: 2, label: 'En acquisition', short: 'A', color: '#fff9c4', textColor: '#f57f17' },
  { value: 3, label: 'Acquis', short: 'V', color: '#c8e6c9', textColor: '#2e7d32' },
];

const API_DOMAIN = process.env.NEXT_PUBLIC_ENTRYPOINT || "";

const ProgressGrid = () => {
  const record = useRecordContext();
  const { session } = useSessionContext();
  const notify = useNotify();
  const refresh = useRefresh();
  const [progresses, setProgresses] = useState<any[]>([]);

  useEffect(() => {
    if (record?.progresses) {
      setProgresses([...record.progresses].sort((a, b) => {
        const posA = a.lesson?.position ?? 0;
        const posB = b.lesson?.position ?? 0;
        return posA - posB;
      }));
    }
  }, [record?.progresses]);

  if (!record || !progresses.length) return null;

  const handleNiveauChange = async (progressId: number | string, newNiveau: number) => {
    const numId = typeof progressId === 'string' ? progressId.replace(/.*\//, '') : progressId;

    try {
      const res = await fetch(`${API_DOMAIN}/progresses/${numId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/merge-patch+json',
          Authorization: `Bearer ${session?.accessToken}`,
          ...(() => { try { const c = JSON.parse(sessionStorage.getItem('client') || '{}'); return c?.id ? { 'X-Client-Id': String(c.id) } : {}; } catch { return {}; } })(),
        },
        body: JSON.stringify({ niveau: newNiveau }),
      });

      if (!res.ok) throw new Error('Erreur PATCH');

      setProgresses(prev =>
        prev.map(p => {
          const pId = typeof p.id === 'string' ? p.id.replace(/.*\//, '') : p.id;
          return String(pId) === String(numId) ? { ...p, niveau: newNiveau } : p;
        })
      );
      notify('Progression mise à jour', { type: 'success' });
    } catch {
      notify('Erreur lors de la mise à jour', { type: 'error' });
    }
  };

  const handleCommentChange = async (progressId: number | string, commentaire: string) => {
    const numId = typeof progressId === 'string' ? progressId.replace(/.*\//, '') : progressId;

    try {
      await fetch(`${API_DOMAIN}/progresses/${numId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/merge-patch+json',
          Authorization: `Bearer ${session?.accessToken}`,
          ...(() => { try { const c = JSON.parse(sessionStorage.getItem('client') || '{}'); return c?.id ? { 'X-Client-Id': String(c.id) } : {}; } catch { return {}; } })(),
        },
        body: JSON.stringify({ commentaire }),
      });
    } catch {
      notify('Erreur lors de la sauvegarde du commentaire', { type: 'error' });
    }
  };

  const totalNiveau = progresses.reduce((s, p) => s + (p.niveau ?? 0), 0);
  const maxNiveau = progresses.length * 3;
  const percent = maxNiveau > 0 ? Math.round((totalNiveau / maxNiveau) * 100 * 10) / 10 : 0;

  return (
    <Box sx={{ mt: 3, width: '100%' }}>
      <Divider sx={{ mb: 2 }} />
      <Box sx={{ display: 'flex', alignItems: 'center', gap: 2, mb: 2 }}>
        <Typography variant="h6">Progression des leçons</Typography>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, ml: 'auto' }}>
          <LinearProgress
            variant="determinate"
            value={percent}
            color={percent >= 100 ? 'success' : percent >= 50 ? 'warning' : 'primary'}
            sx={{ width: 150, height: 10, borderRadius: 5 }}
          />
          <Typography variant="body2" fontWeight={600}>{percent}%</Typography>
        </Box>
      </Box>

      <Box sx={{ display: 'flex', gap: 1, mb: 2 }}>
        {niveauConfig.map(n => (
          <Chip
            key={n.value}
            label={`${n.short} ${n.label}`}
            size="small"
            sx={{ backgroundColor: n.color, color: n.textColor, fontWeight: 600 }}
          />
        ))}
      </Box>

      <TableContainer component={Paper} variant="outlined">
        <Table size="small">
          <TableHead>
            <TableRow sx={{ backgroundColor: '#f5f5f5' }}>
              <TableCell sx={{ fontWeight: 600 }}>Leçon</TableCell>
              <TableCell sx={{ fontWeight: 600 }}>Type</TableCell>
              <TableCell sx={{ fontWeight: 600, textAlign: 'center' }}>Niveau</TableCell>
              <TableCell sx={{ fontWeight: 600 }}>Commentaire instructeur</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {progresses.map((p) => {
              const pId = typeof p.id === 'string' ? p.id.replace(/.*\//, '') : p.id;
              const currentNiveau = niveauConfig[p.niveau ?? 0];

              return (
                <TableRow
                  key={pId}
                  sx={{ backgroundColor: currentNiveau.color + '30' }}
                >
                  <TableCell>
                    <Typography variant="body2" fontWeight={500}>
                      {p.lesson?.nom ?? p.lesson?.name ?? '—'}
                    </Typography>
                  </TableCell>
                  <TableCell>
                    <Chip
                      label={p.lesson?.type === 'theorie' ? 'Théorie' : p.lesson?.type === 'mixte' ? 'Mixte' : 'Pratique'}
                      size="small"
                      variant="outlined"
                    />
                  </TableCell>
                  <TableCell align="center">
                    <ToggleButtonGroup
                      value={p.niveau ?? 0}
                      exclusive
                      onChange={(_, val) => val !== null && handleNiveauChange(pId, val)}
                      size="small"
                    >
                      {niveauConfig.map(n => (
                        <Tooltip key={n.value} title={n.label}>
                          <ToggleButton
                            value={n.value}
                            sx={{
                              px: 1.5,
                              py: 0.5,
                              fontWeight: 700,
                              fontSize: '0.8rem',
                              backgroundColor: (p.niveau ?? 0) === n.value ? n.color : 'transparent',
                              color: (p.niveau ?? 0) === n.value ? n.textColor : '#999',
                              '&.Mui-selected': { backgroundColor: n.color, color: n.textColor },
                              '&.Mui-selected:hover': { backgroundColor: n.color },
                            }}
                          >
                            {n.short}
                          </ToggleButton>
                        </Tooltip>
                      ))}
                    </ToggleButtonGroup>
                  </TableCell>
                  <TableCell>
                    <TextField
                      defaultValue={p.commentaire ?? ''}
                      placeholder="Commentaire..."
                      size="small"
                      fullWidth
                      multiline
                      maxRows={2}
                      onBlur={(e) => handleCommentChange(pId, e.target.value)}
                      variant="standard"
                      sx={{ '& .MuiInput-underline:before': { borderBottomColor: '#e0e0e0' } }}
                    />
                  </TableCell>
                </TableRow>
              );
            })}
          </TableBody>
        </Table>
      </TableContainer>
    </Box>
  );
};

export const TrainingEdit = () => {
  const { session } = useSessionContext();

  const transform = async ({ documents, ...data }: any) => {
    const extract = (val: any) => typeof val === 'object' && val?.['@id'] ? val['@id'] : val;

    const documentIds = isDefinedAndNotVoid(documents)
      ? await syncMediaDocuments(
          documents.map((d: any) => d?.['@id'] ? d : { ...d, description: d.title }),
          session
        )
      : [];

    return {
      ...data,
      eleve: extract(data.eleve),
      instructeur: extract(data.instructeur),
      programme: extract(data.programme),
      documents: documentIds,
    };
  };

  return (
    <Edit transform={transform}>
      <SimpleForm>
        <ReferenceInput source="eleve.@id" reference="users" sort={{ field: 'lastName', order: 'ASC' }}>
          <AutocompleteInput
            optionText={(record: any) => record ? `${record.firstName ?? ''} ${record.lastName ?? ''}`.trim() : ''}
            label="Élève"
            validate={required()}
            fullWidth
          />
        </ReferenceInput>
        <ReferenceInput source="instructeur.@id" reference="users" sort={{ field: 'lastName', order: 'ASC' }} filter={{ encadrant: true }}>
          <AutocompleteInput
            optionText={(record: any) => record ? `${record.firstName ?? ''} ${record.lastName ?? ''}`.trim() : ''}
            label="Instructeur"
            fullWidth
          />
        </ReferenceInput>
        <ReferenceInput source="programme.@id" reference="programmes" filter={{ isAvailable: true }}>
          <AutocompleteInput optionText="nom" label="Programme de formation" validate={required()} fullWidth />
        </ReferenceInput>
        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
          <Box flex={1}>
            <DateInput source="dateDebut" label="Date de début" fullWidth />
          </Box>
          <Box flex={1}>
            <DateInput source="dateFin" label="Date de fin" fullWidth />
          </Box>
        </Box>
        <SelectInput source="statut" label="Statut" choices={statutChoices} />
        <FileInput source="documents" multiple={true} label="Documents (certificats, attestations...)">
          <MyFileField source="contentUrl" />
        </FileInput>
        <ProgressGrid />
      </SimpleForm>
    </Edit>
  );
};
