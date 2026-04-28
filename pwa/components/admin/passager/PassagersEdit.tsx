import { DateInput, Edit, NumberInput } from "react-admin";
import { SimpleForm, TextInput } from "react-admin";

export const PassagersEdit = () => {

  return (
  <Edit >
      <SimpleForm>
          <DateInput source="date" label="Date" />
          <TextInput source="nom" label="Nom" />
          <TextInput source="prenom" label="Prénom" />
          <TextInput source="telephone" label="N° de téléphone" />
          <TextInput source="email" label="Adresse email" />
          <NumberInput source="poids" label="Poids (kg)" min={20} max={200} />
        </SimpleForm>
  </Edit>
  )
};