import { SimpleForm, TextInput, NumberInput, Edit, required, BooleanInput } from "react-admin";

export const OptionsEdit = () => {

  return (
    <Edit>
        <SimpleForm>
            <TextInput source="nom" label="Nom de l'option" validate={required()}/>
            <NumberInput source="prix" label="Prix" validate={required()}/>
            <BooleanInput source="isAvailable" label="Disponible" />
        </SimpleForm>
    </Edit>
  )
};
