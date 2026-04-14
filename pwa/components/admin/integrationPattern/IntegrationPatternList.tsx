import {
  List,
  Datagrid,
  TextField,
  BooleanField,
  DateField,
  EditButton,
  DeleteButton,
} from "react-admin";

export const IntegrationPatternList = () => (
  <List sort={{ field: "name", order: "ASC" }} perPage={25}>
    <Datagrid rowClick="edit">
      <TextField source="name" label="Nom" />
      <TextField source="code" label="Code" />
      <TextField source="capability" label="Capability" />
      <TextField source="requiredModule" label="Module requis" />
      <TextField source="method" label="Méthode" />
      <TextField source="urlTemplate" label="URL Template" />
      <BooleanField source="active" label="Actif" />
      <DateField source="createdAt" label="Créé le" />
      <EditButton />
      <DeleteButton />
    </Datagrid>
  </List>
);
