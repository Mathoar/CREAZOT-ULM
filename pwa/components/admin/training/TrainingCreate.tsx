import {
  SimpleForm,
  Create,
  required,
  SelectInput,
  ReferenceInput,
  AutocompleteInput,
  DateInput,
} from "react-admin";

const statutChoices = [
  { id: 'en_cours', name: 'En cours' },
  { id: 'termine', name: 'Terminé' },
  { id: 'abandonne', name: 'Abandonné' },
];

export const TrainingCreate = () => (
  <Create>
    <SimpleForm>
      <ReferenceInput source="eleve" reference="users" sort={{ field: 'lastName', order: 'ASC' }}>
        <AutocompleteInput
          optionText={(record: any) => record ? `${record.firstName ?? ''} ${record.lastName ?? ''}`.trim() : ''}
          label="Élève"
          validate={required()}
          fullWidth
        />
      </ReferenceInput>
      <ReferenceInput source="instructeur" reference="users" sort={{ field: 'lastName', order: 'ASC' }} filter={{ encadrant: true }}>
        <AutocompleteInput
          optionText={(record: any) => record ? `${record.firstName ?? ''} ${record.lastName ?? ''}`.trim() : ''}
          label="Instructeur"
          fullWidth
        />
      </ReferenceInput>
      <ReferenceInput source="programme" reference="programmes" filter={{ isAvailable: true }}>
        <AutocompleteInput optionText="nom" label="Programme de formation" validate={required()} fullWidth />
      </ReferenceInput>
      <DateInput source="dateDebut" label="Date de début" defaultValue={new Date().toISOString().split('T')[0]} />
      <SelectInput source="statut" label="Statut" choices={statutChoices} defaultValue="en_cours" />
    </SimpleForm>
  </Create>
);
