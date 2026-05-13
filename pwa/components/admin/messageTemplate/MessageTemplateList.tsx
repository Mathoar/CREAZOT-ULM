import {
  List,
  Datagrid,
  TextField,
  DeleteButton,
  FunctionField,
} from "react-admin";
import { Chip } from "@mui/material";
import SmsIcon from "@mui/icons-material/Sms";
import EmailIcon from "@mui/icons-material/Email";
import { ProtectedEditButton } from "../PermissionGuards";

export const MessageTemplateList = () => (
  <List sort={{ field: "title", order: "ASC" }} perPage={25}>
    <Datagrid rowClick="edit">
      <TextField source="title" label="Titre" />
      <FunctionField
        label="Canal"
        render={(record: any) =>
          record?.isSmsMessage ? (
            <Chip icon={<SmsIcon fontSize="small" />} label="SMS" size="small" color="primary" variant="outlined" />
          ) : (
            <Chip icon={<EmailIcon fontSize="small" />} label="Email" size="small" color="default" variant="outlined" />
          )
        }
      />
      <TextField source="body" label="Message" />
      <ProtectedEditButton />
      <DeleteButton />
    </Datagrid>
  </List>
);
