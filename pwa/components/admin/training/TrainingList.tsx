import {
  Datagrid,
  List,
  TextField,
  ExportButton,
  TopToolbar,
  SimpleList,
  ShowButton,
  FunctionField,
  ReferenceField,
  DateField,
} from "react-admin";
import { useMediaQuery, Theme, Chip, LinearProgress, Box, Typography } from '@mui/material';
import { ProtectedCreateButton, ProtectedEditButton } from "../PermissionGuards";

const statutLabels: Record<string, { label: string; color: 'success' | 'warning' | 'default' | 'error' }> = {
  en_cours: { label: 'En cours', color: 'warning' },
  termine: { label: 'Terminé', color: 'success' },
  abandonne: { label: 'Abandonné', color: 'error' },
};

const ProgressBar = ({ record }: { record?: any }) => {
  const percent = record?.progressionPercent ?? 0;
  const color = percent >= 100 ? 'success' : percent >= 50 ? 'warning' : 'primary';
  return (
    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, minWidth: 120 }}>
      <LinearProgress variant="determinate" value={percent} color={color} sx={{ flex: 1, height: 8, borderRadius: 4 }} />
      <Typography variant="caption" sx={{ minWidth: 40, textAlign: 'right' }}>{percent}%</Typography>
    </Box>
  );
};

const ListActions = () => (
  <TopToolbar>
    <ProtectedCreateButton />
    <ExportButton />
  </TopToolbar>
);

export const TrainingList = () => {
  const isSmall = useMediaQuery<Theme>(theme => theme.breakpoints.down('sm'));

  return (
    <List resource="trainings" actions={<ListActions />} sort={{ field: 'dateDebut', order: 'DESC' }}>
      {isSmall ? (
        <SimpleList
          primaryText={record => {
            const eleve = record?.eleve;
            return eleve ? `${eleve.firstName ?? ''} ${eleve.lastName ?? ''}`.trim() : '—';
          }}
          secondaryText={record => record?.programme?.nom ?? '—'}
          tertiaryText={record => `${record?.progressionPercent ?? 0}%`}
          linkType="show"
        />
      ) : (
        <Datagrid sx={{ '& .RaDatagrid-headerCell': { backgroundColor: '#ededed', fontWeight: 'lighter' } }}>
          <FunctionField
            label="Élève"
            render={record => {
              const e = record?.eleve;
              return e ? `${e.firstName ?? ''} ${e.lastName ?? ''}`.trim() : '—';
            }}
            sortable
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
            label="Progression"
            render={record => <ProgressBar record={record} />}
          />
          <FunctionField
            label="Statut"
            render={record => {
              const info = statutLabels[record?.statut] ?? { label: record?.statut, color: 'default' };
              return <Chip label={info.label} color={info.color} size="small" />;
            }}
          />
          <DateField source="dateDebut" label="Début" />
          <p className="text-right">
            <ShowButton />
            <ProtectedEditButton />
          </p>
        </Datagrid>
      )}
    </List>
  );
};
