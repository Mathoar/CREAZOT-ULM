import {
  Show,
  SimpleShowLayout,
  TextField,
  DateField,
  FunctionField,
} from "react-admin";
import { ProtectedShowActions } from "../PermissionGuards";
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
} from "@mui/material";

const statutLabels: Record<string, { label: string; color: 'success' | 'warning' | 'error' | 'default' }> = {
  en_cours: { label: 'En cours', color: 'warning' },
  termine: { label: 'Terminé', color: 'success' },
  abandonne: { label: 'Abandonné', color: 'error' },
};

const niveauConfig = [
  { value: 0, label: 'Non abordé', color: '#e0e0e0', textColor: '#666' },
  { value: 1, label: 'Présenté', color: '#ffcdd2', textColor: '#c62828' },
  { value: 2, label: 'En acquisition', color: '#fff9c4', textColor: '#f57f17' },
  { value: 3, label: 'Acquis', color: '#c8e6c9', textColor: '#2e7d32' },
];

export const TrainingShow = () => (
  <Show actions={<ProtectedShowActions />}>
    <SimpleShowLayout>
      <FunctionField
        label="Élève"
        render={record => {
          const e = record?.eleve;
          return e ? `${e.firstName ?? ''} ${e.lastName ?? ''}`.trim() : '—';
        }}
      />
      <FunctionField
        label="Instructeur"
        render={record => {
          const i = record?.instructeur;
          return i ? `${i.firstName ?? ''} ${i.lastName ?? ''}`.trim() : '—';
        }}
      />
      <FunctionField
        label="Programme"
        render={record => record?.programme?.nom ?? '—'}
      />
      <FunctionField
        label="Statut"
        render={record => {
          const info = statutLabels[record?.statut] ?? { label: record?.statut, color: 'default' };
          return <Chip label={info.label} color={info.color} size="small" />;
        }}
      />
      <DateField source="dateDebut" label="Date de début" />
      <DateField source="dateFin" label="Date de fin" emptyText="—" />
      <FunctionField
        label="Progression globale"
        render={record => {
          const percent = record?.progressionPercent ?? 0;
          return (
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
              <LinearProgress
                variant="determinate"
                value={percent}
                color={percent >= 100 ? 'success' : percent >= 50 ? 'warning' : 'primary'}
                sx={{ width: 200, height: 10, borderRadius: 5 }}
              />
              <Typography variant="body1" fontWeight={600}>{percent}%</Typography>
            </Box>
          );
        }}
      />

      <FunctionField
        label=""
        render={record => {
          const progresses = record?.progresses ?? [];
          if (!progresses.length) return null;

          return (
            <Box sx={{ mt: 2, width: '100%' }}>
              <Typography variant="h6" sx={{ mb: 2 }}>Détail par leçon</Typography>
              <TableContainer component={Paper} variant="outlined">
                <Table size="small">
                  <TableHead>
                    <TableRow sx={{ backgroundColor: '#f5f5f5' }}>
                      <TableCell sx={{ fontWeight: 600 }}>Leçon</TableCell>
                      <TableCell sx={{ fontWeight: 600 }}>Type</TableCell>
                      <TableCell sx={{ fontWeight: 600 }}>Niveau</TableCell>
                      <TableCell sx={{ fontWeight: 600 }}>Commentaire</TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {progresses.map((p: any, idx: number) => {
                      const n = niveauConfig[p.niveau ?? 0];
                      return (
                        <TableRow key={idx} sx={{ backgroundColor: n.color + '30' }}>
                          <TableCell>{p.lesson?.nom ?? p.lesson?.name ?? '—'}</TableCell>
                          <TableCell>
                            <Chip
                              label={p.lesson?.type === 'theorie' ? 'Théorie' : p.lesson?.type === 'mixte' ? 'Mixte' : 'Pratique'}
                              size="small"
                              variant="outlined"
                            />
                          </TableCell>
                          <TableCell>
                            <Chip label={n.label} size="small" sx={{ backgroundColor: n.color, color: n.textColor, fontWeight: 600 }} />
                          </TableCell>
                          <TableCell>
                            <Typography variant="body2" color="text.secondary">
                              {p.commentaire || '—'}
                            </Typography>
                          </TableCell>
                        </TableRow>
                      );
                    })}
                  </TableBody>
                </Table>
              </TableContainer>
            </Box>
          );
        }}
      />

      <DateField source="createdAt" label="Créé le" showTime />
      <DateField source="updatedAt" label="Mis à jour le" showTime />
    </SimpleShowLayout>
  </Show>
);
