import { Show, SimpleShowLayout, TextField, BooleanField, RichTextField, FunctionField, DateField } from "react-admin";
import { ProtectedShowActions } from "../PermissionGuards";
import { Chip } from "@mui/material";
import { DocumentListField } from "../shared/OdooDocumentField";

const lessonTypeLabels: Record<string, { label: string; color: 'primary' | 'secondary' | 'default' }> = {
  pratique: { label: 'Pratique', color: 'primary' },
  theorie: { label: 'Théorie', color: 'secondary' },
  mixte: { label: 'Mixte', color: 'default' },
};

export const LessonShow = () => (
  <Show actions={<ProtectedShowActions />}>
    <SimpleShowLayout>
      <TextField source="categorie" label="Catégorie / Thème" />
      <TextField source="nom" label="Nom de la leçon" />
      <FunctionField
        label="Type"
        render={record => {
          const info = lessonTypeLabels[record?.type] ?? { label: record?.type, color: 'default' };
          return <Chip label={info.label} color={info.color} size="small" />;
        }}
      />
      <RichTextField source="briefing" label="Briefing / Contenu pédagogique" />
      <BooleanField source="isAvailable" label="Disponible" />
      <DocumentListField source="documents" label="Documents associés" />
      <DateField source="createdAt" label="Créé le" showTime />
      <DateField source="updatedAt" label="Mis à jour le" showTime />
    </SimpleShowLayout>
  </Show>
);
