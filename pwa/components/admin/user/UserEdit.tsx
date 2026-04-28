import {
  Edit,
  SimpleForm,
  TextInput,
  ReferenceArrayInput,
  AutocompleteArrayInput,
} from "react-admin";
import { Typography } from "@mui/material";

const transform = (data: any) => ({
  ...data,
  clients: (data.clients || []).map((c: any) =>
    typeof c === "string" ? c : c["@id"] || c
  ),
});

export const UserEdit = () => (
  <Edit mutationMode="pessimistic" transform={transform}>
    <SimpleForm>
      <TextInput source="firstName" label="Prénom" />
      <TextInput source="lastName" label="Nom" />
      <TextInput source="email" label="Email" disabled />

      <Typography variant="subtitle1" sx={{ mt: 2, mb: 1, fontWeight: 600 }}>
        Clients rattachés
      </Typography>

      <ReferenceArrayInput source="clients" reference="clients">
        <AutocompleteArrayInput
          optionText="name"
          label="Clients"
          filterToQuery={(q) => ({ name: q })}
          fullWidth
        />
      </ReferenceArrayInput>
    </SimpleForm>
  </Edit>
);
