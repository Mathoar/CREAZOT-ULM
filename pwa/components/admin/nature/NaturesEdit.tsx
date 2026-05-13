import { SimpleForm, TextInput, BooleanInput, Edit, required } from "react-admin"; 

export const NaturesEdit = () => {

  return (
    <Edit>
        <SimpleForm>
          <TextInput source="code" label="Code" validate={required()}/>
          <TextInput source="label" label="Label" validate={required()}/>
          <BooleanInput source="isParticularActivity" label="Activité Particulière (AP)" />
          <BooleanInput source="needsEncadrant" label="Encadrant requis" />
          <BooleanInput source="encadrantOptional" label="Encadrant optionnel (sélectionnable, non obligatoire)" />
        </SimpleForm>
    </Edit>
  )
};