import { useState, useEffect } from "react";
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
  TextInput,
  SelectInput,
  Form,
  useListContext,
} from "react-admin";
import { useMediaQuery, Theme, Chip, Box } from '@mui/material';
import { ProtectedCreateButton, ProtectedEditButton } from "../PermissionGuards";

const lessonTypeLabels: Record<string, { label: string; color: 'primary' | 'secondary' | 'default' }> = {
  pratique: { label: 'Pratique', color: 'primary' },
  theorie: { label: 'Théorie', color: 'secondary' },
  mixte: { label: 'Mixte', color: 'default' },
};

const ListActions = () => (
  <TopToolbar>
    <ProtectedCreateButton />
    <ExportButton />
  </TopToolbar>
);

const CustomFilterBar = () => {
  const { filterValues, setFilters } = useListContext();
  const [formValues, setFormValues] = useState({
    categorie: filterValues['categorie'] || '',
    type: filterValues['type'] || '',
  });

  useEffect(() => {
    setFormValues({
      categorie: filterValues['categorie'] || '',
      type: filterValues['type'] || '',
    });
  }, [filterValues]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    const newValues = { ...formValues, [name]: value };
    setFormValues(newValues);
    setFilters(newValues);
  };

  return (
    <Form>
      <Box display="flex" flexWrap="wrap" columnGap={2} rowGap={0.5} mt={1} alignItems="flex-end">
        <TextInput
          source="categorie"
          label="Catégorie"
          onChange={handleChange}
          defaultValue={formValues['categorie']}
          sx={{ width: 200 }}
        />
        <SelectInput
          source="type"
          label="Type"
          onChange={handleChange}
          defaultValue={formValues['type']}
          choices={[
            { id: 'pratique', name: 'Pratique' },
            { id: 'theorie', name: 'Théorie' },
            { id: 'mixte', name: 'Mixte' },
          ]}
          emptyText="Tous"
          sx={{ width: 160 }}
        />
      </Box>
    </Form>
  );
};

export const LessonList = () => {
  const isSmall = useMediaQuery<Theme>(theme => theme.breakpoints.down('sm'));

  return (
    <List
      resource="lessons"
      actions={<ListActions />}
      filters={<CustomFilterBar />}
      filterDefaultValues={{}}
      perPage={50}
      disableSyncWithLocation
    >
      {isSmall ? (
        <SimpleList
          primaryText={record => record.nom}
          secondaryText={record => `${record.categorie ?? ''} — ${lessonTypeLabels[record.type]?.label ?? record.type}`}
          linkType="edit"
        />
      ) : (
        <Datagrid sx={{ '& .RaDatagrid-headerCell': { backgroundColor: '#ededed', fontWeight: 'lighter' } }}>
          <TextField source="categorie" label="Catégorie" sortable />
          <TextField source="nom" label="Nom de la leçon" sortable />
          <FunctionField
            label="Type"
            render={record => {
              const info = lessonTypeLabels[record.type] ?? { label: record.type, color: 'default' };
              return <Chip label={info.label} color={info.color} size="small" />;
            }}
          />
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
