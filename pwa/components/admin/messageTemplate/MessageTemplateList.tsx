import {
  List,
  Datagrid,
  TextField,
  DeleteButton,
} from "react-admin";
import { ProtectedEditButton } from "../PermissionGuards";

export const MessageTemplateList = () => (
  <List sort={{ field: "title", order: "ASC" }} perPage={25}>
    <Datagrid rowClick="edit">
      <TextField source="title" label="Titre" />
      <TextField source="body" label="Message" />
      <ProtectedEditButton />
      <DeleteButton />
    </Datagrid>
  </List>
);
