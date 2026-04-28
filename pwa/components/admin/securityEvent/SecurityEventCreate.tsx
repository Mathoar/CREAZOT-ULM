import {
  Create,
  SimpleForm,
  TextInput,
  SelectInput,
  DateTimeInput,
  ReferenceInput,
  AutocompleteInput,
  required,
} from "react-admin";

const typeChoices = [
  { id: "incident", name: "Incident" },
  { id: "accident", name: "Accident" },
  { id: "quasi_accident", name: "Quasi-accident" },
  { id: "observation", name: "Observation" },
  { id: "note_interne", name: "Note interne" },
];

export const SecurityEventCreate = () => (
  <Create redirect="list">
    <SimpleForm>
      <SelectInput source="type" label="Type d'événement" choices={typeChoices} validate={required()} />
      <DateTimeInput source="dateEvenement" label="Date de l'événement" validate={required()} defaultValue={new Date().toISOString()} />
      <ReferenceInput source="pilote" reference="users" sort={{ field: "lastName", order: "ASC" }}>
        <AutocompleteInput
          optionText={(r: any) => r ? `${r.firstName ?? ""} ${r.lastName ?? ""}` : ""}
          label="Pilote concerné"
          validate={required()}
        />
      </ReferenceInput>
      <ReferenceInput source="aeronef" reference="aeronefs" sort={{ field: "immatriculation", order: "ASC" }}>
        <AutocompleteInput optionText="immatriculation" label="Aéronef (optionnel)" />
      </ReferenceInput>
      <TextInput source="description" label="Description de l'événement" multiline rows={4} fullWidth validate={required()} />
    </SimpleForm>
  </Create>
);
