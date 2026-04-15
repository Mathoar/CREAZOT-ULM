import {
  Create,
  SimpleForm,
  TextInput,
  BooleanInput,
  NumberInput,
  CheckboxGroupInput,
  required,
} from "react-admin";
import { useModuleChoices } from "./useModuleChoices";

export const ModulePacksCreate = () => {
  const { choices, loading } = useModuleChoices();

  return (
    <Create>
      <SimpleForm>
        <TextInput source="name" label="Nom" validate={required()} />
        <TextInput source="slug" label="Slug" validate={required()} />
        <TextInput source="description" label="Description" multiline />
        <BooleanInput source="isDefault" label="Pack par défaut" />
        <NumberInput source="sortOrder" label="Ordre d'affichage" defaultValue={0} />
        <CheckboxGroupInput source="modules" label="Modules inclus" choices={choices} helperText={loading ? "Chargement des modules..." : false} />
      </SimpleForm>
    </Create>
  );
};
