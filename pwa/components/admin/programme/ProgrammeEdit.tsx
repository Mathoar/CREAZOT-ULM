import {
  SimpleForm,
  TextInput,
  Edit,
  required,
  BooleanInput,
  SelectInput,
  ArrayInput,
  SimpleFormIterator,
  ReferenceInput,
  AutocompleteInput,
  NumberInput,
  FormDataConsumer,
  useGetOne,
  useRecordContext,
} from "react-admin";
import { Typography, TextField as MuiTextField, Box } from "@mui/material";

const programmeTypes = [
  { id: 'brevet', name: 'Brevet' },
  { id: 'qualification', name: 'Qualification' },
  { id: 'perfectionnement', name: 'Perfectionnement' },
  { id: 'transition', name: 'Transition machine' },
];

const typeLabels: Record<string, string> = {
  pratique: 'Pratique',
  theorie: 'Théorique',
  mixte: 'Mixte',
};

const LessonOptionText = () => {
  const record = useRecordContext();
  if (!record) return null;

  return (
    <Box>
      <Box component="span">{record.nom}</Box>
      {record.categorie && (
        <Box component="span" sx={{ ml: 1, fontStyle: 'italic', fontSize: '0.8em', color: '#888' }}>
          {record.categorie}
        </Box>
      )}
      {record.type && (
        <Box sx={{ fontSize: '0.75em', color: '#aaa' }}>
          {typeLabels[record.type] || record.type}
        </Box>
      )}
    </Box>
  );
};

const lessonInputText = (record: any) => record?.nom || '';

const lessonMatchSuggestion = (filter: string, choice: any) => {
  const search = filter.toLowerCase();
  return (
    choice.nom?.toLowerCase().includes(search) ||
    choice.categorie?.toLowerCase().includes(search)
  );
};

const LessonCategorieDisplay = ({ lessonIri }: { lessonIri?: string }) => {
  const { data } = useGetOne('lessons', { id: lessonIri }, { enabled: !!lessonIri });

  return (
    <MuiTextField
      label="Catégorie"
      value={data?.categorie || ''}
      disabled
      size="small"
      sx={{
        minWidth: 160,
        '& .MuiInputBase-input.Mui-disabled': { WebkitTextFillColor: '#555' },
      }}
    />
  );
};

const transformProgramme = (data: any) => ({
  ...data,
  programmeLessons: (data.programmeLessons || []).map((pl: any) => {
    const lessonIri = typeof pl.lesson === 'object' ? pl.lesson['@id'] : pl.lesson;
    const cleaned: any = { lesson: lessonIri, position: pl.position || 0 };
    if (pl['@id']) cleaned['@id'] = pl['@id'];
    return cleaned;
  }),
});

export const ProgrammeEdit = () => (
  <Edit transform={transformProgramme}>
    <SimpleForm>
      <TextInput source="nom" label="Nom du programme" validate={required()} fullWidth />
      <SelectInput source="type" label="Type de formation" choices={programmeTypes} validate={required()} />
      <TextInput source="description" label="Description" multiline rows={3} fullWidth />
      <BooleanInput source="isAvailable" label="Disponible" />
      <Typography variant="subtitle1" sx={{ mt: 2, mb: 1, fontWeight: 600 }}>
        Leçons du programme
      </Typography>
      <ArrayInput source="programmeLessons" label="">
        <SimpleFormIterator inline disableReordering={false}>
          <ReferenceInput source="lesson.@id" reference="lessons" filter={{ isAvailable: true, pagination: false }}>
            <AutocompleteInput
              optionText={<LessonOptionText />}
              inputText={lessonInputText}
              matchSuggestion={lessonMatchSuggestion}
              label="Leçon"
              sx={{ minWidth: 350 }}
              validate={required()}
            />
          </ReferenceInput>
          <FormDataConsumer>
            {({ scopedFormData }) => {
              const lessonIri = scopedFormData?.lesson?.['@id'] || scopedFormData?.lesson;
              return <LessonCategorieDisplay lessonIri={lessonIri} />;
            }}
          </FormDataConsumer>
          <NumberInput source="position" label="Ordre" defaultValue={0} sx={{ maxWidth: 80 }} />
        </SimpleFormIterator>
      </ArrayInput>
    </SimpleForm>
  </Edit>
);
