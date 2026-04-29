import {
  Datagrid,
  List,
  TextField,
  ExportButton,
  TopToolbar,
  SimpleList,
  ShowButton,
  BooleanField,
  FunctionField,
  NumberField,
} from "react-admin";
import { useMediaQuery, Theme, Chip } from '@mui/material';
import { ProtectedCreateButton, ProtectedEditButton } from "../PermissionGuards";

const programmeTypeLabels: Record<string, string> = {
  brevet: 'Brevet',
  qualification: 'Qualification',
  perfectionnement: 'Perfectionnement',
  transition: 'Transition machine',
};

const ListActions = () => (
  <TopToolbar>
    <ProtectedCreateButton />
    <ExportButton />
  </TopToolbar>
);

export const ProgrammeList = () => {
  const isSmall = useMediaQuery<Theme>(theme => theme.breakpoints.down('sm'));

  return (
    <List resource="programmes" actions={<ListActions />}>
      {isSmall ? (
        <SimpleList
          primaryText={record => record.nom}
          secondaryText={record => programmeTypeLabels[record.type] ?? record.type}
          tertiaryText={record => `${record.lessonCount ?? 0} leçon(s)`}
          linkType="edit"
        />
      ) : (
        <Datagrid sx={{ '& .RaDatagrid-headerCell': { backgroundColor: '#ededed', fontWeight: 'lighter' } }}>
          <TextField source="nom" label="Nom du programme" sortable />
          <FunctionField
            label="Type"
            render={record => (
              <Chip
                label={programmeTypeLabels[record.type] ?? record.type}
                size="small"
                variant="outlined"
              />
            )}
          />
          <NumberField source="lessonCount" label="Leçons" />
          <BooleanField source="isAvailable" label="Disponible" />
          <p className="text-right">
            <ShowButton />
            <ProtectedEditButton />
          </p>
        </Datagrid>
      )}
    </List>
  );
};
