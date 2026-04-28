import {
  Show,
  SimpleShowLayout,
  TextField,
  BooleanField,
  DateField,
  FunctionField,
  ArrayField,
  Datagrid,
  NumberField,
  ReferenceField,
} from "react-admin";
import { Chip, Typography } from "@mui/material";

const programmeTypeLabels: Record<string, string> = {
  brevet: 'Brevet',
  qualification: 'Qualification',
  perfectionnement: 'Perfectionnement',
  transition: 'Transition machine',
};

export const ProgrammeShow = () => (
  <Show>
    <SimpleShowLayout>
      <TextField source="nom" label="Nom du programme" />
      <FunctionField
        label="Type"
        render={record => (
          <Chip
            label={programmeTypeLabels[record?.type] ?? record?.type}
            size="small"
            variant="outlined"
          />
        )}
      />
      <TextField source="description" label="Description" />
      <BooleanField source="isAvailable" label="Disponible" />
      <NumberField source="lessonCount" label="Nombre de leçons" />
      <Typography variant="subtitle1" sx={{ mt: 2, mb: 1, fontWeight: 600 }}>
        Leçons du programme
      </Typography>
      <ArrayField source="programmeLessons" label="">
        <Datagrid bulkActionButtons={false} sx={{ '& .RaDatagrid-headerCell': { backgroundColor: '#f5f5f5' } }}>
          <NumberField source="position" label="#" />
          <FunctionField label="Leçon" render={record => record?.lesson?.nom ?? '—'} />
          <FunctionField
            label="Type"
            render={record => {
              const type = record?.lesson?.type;
              const labels: Record<string, string> = { pratique: 'Pratique', theorie: 'Théorie', mixte: 'Mixte' };
              return type ? <Chip label={labels[type] ?? type} size="small" variant="outlined" /> : '—';
            }}
          />
        </Datagrid>
      </ArrayField>
      <DateField source="createdAt" label="Créé le" showTime />
      <DateField source="updatedAt" label="Mis à jour le" showTime />
    </SimpleShowLayout>
  </Show>
);
