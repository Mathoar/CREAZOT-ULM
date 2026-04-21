import {
  List,
  Datagrid,
  TextField,
  EditButton,
  DeleteButton,
} from "react-admin";

export const MessageTemplateList = () => (
  <List sort={{ field: "title", order: "ASC" }} perPage={25}>
    <Datagrid rowClick="edit">
      <TextField source="title" label="Titre" />
      <TextField source="body" label="Message" />
      <EditButton />
      <DeleteButton />
    </Datagrid>
  </List>
);
