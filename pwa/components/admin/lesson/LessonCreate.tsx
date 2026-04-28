import { SimpleForm, TextInput, Create, required, BooleanInput, SelectInput, FileInput } from "react-admin";
import { RichTextInput } from "ra-input-rich-text";
import { MyFileField } from "../shared/OdooDocumentField";
import { useSessionContext } from "../SessionContextProvider";
import { syncMediaDocuments } from "../../../app/lib/client";
import { isDefinedAndNotVoid } from "../../../app/lib/utils";

const lessonTypes = [
  { id: 'pratique', name: 'Pratique' },
  { id: 'theorie', name: 'Théorie' },
  { id: 'mixte', name: 'Mixte' },
];

export const LessonCreate = () => {
  const { session } = useSessionContext();

  const transform = async ({ documents, ...data }: any) => {
    const documentIds = isDefinedAndNotVoid(documents)
      ? await syncMediaDocuments(
          documents.map((d: any) => ({ ...d, description: d.title })),
          session
        )
      : [];
    return { ...data, documents: documentIds };
  };

  return (
    <Create transform={transform}>
      <SimpleForm>
        <TextInput source="categorie" label="Catégorie / Thème" fullWidth helperText="Ex: Sécurité, Le virage, Navigation..." />
        <TextInput source="nom" label="Nom de la leçon" validate={required()} fullWidth />
        <SelectInput source="type" label="Type" choices={lessonTypes} defaultValue="pratique" validate={required()} />
        <RichTextInput source="briefing" label="Briefing / Contenu pédagogique" fullWidth />
        <BooleanInput source="isAvailable" label="Disponible" defaultValue={true} />
        <FileInput source="documents" multiple={true} label="Documents associés (PDF, images...)">
          <MyFileField source="contentUrl" />
        </FileInput>
      </SimpleForm>
    </Create>
  );
};
