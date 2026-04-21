import { NumberInput, SimpleForm, TextInput, Create, required, BooleanInput } from "react-admin";

export const OptionsCreate = () => {

  return (
      <Create>
          <SimpleForm>
              <TextInput source="nom" label="Nom de l'option" validate={required()}/>
              <NumberInput source="prix" label="Prix" validate={required()}/>
              <BooleanInput source="isAvailable" label="Disponible" defaultValue={true} />
          </SimpleForm>
      </Create>
  )
};
