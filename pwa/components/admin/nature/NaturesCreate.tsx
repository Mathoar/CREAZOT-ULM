import { SimpleForm, TextInput, BooleanInput, Create, required } from "react-admin";

export const NaturesCreate = () => {

  return (
      <Create>
          <SimpleForm>
              <TextInput source="code" label="Code" validate={required()}/>
              <TextInput source="label" label="Label" validate={required()}/>
              <BooleanInput source="isParticularActivity" label="Activité Particulière (AP)" defaultValue={false} />
              <BooleanInput source="needsEncadrant" label="Encadrant requis" defaultValue={false} />
          </SimpleForm>
      </Create>
  )
};