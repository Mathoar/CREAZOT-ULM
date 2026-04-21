import { Edit, SimpleForm, TextInput, BooleanInput, FileInput, useNotify, useRedirect } from "react-admin";
import { RichTextInput } from "ra-input-rich-text";
import { Box, Typography, Alert } from "@mui/material";
import { useClient } from '../ClientProvider';
import { useSessionContext } from "../SessionContextProvider";
import { syncDocument } from "../../../app/lib/client";
import { BriefingFileField, formatMediaObject } from "../shared/BriefingFileField";

export const BriefingsEdit = () => {
  const { client } = useClient();
  const { session } = useSessionContext();
  const notify = useNotify();
  const redirect = useRedirect();

  const transform = async data => {
    const img = data['headerImage'];
    if (img?.rawFile) {
      return { ...data, headerImage: await syncDocument(img, session) };
    }
    if (img?.['@id']) {
      return { ...data, headerImage: img['@id'] };
    }
    if (img === null) {
      return { ...data, headerImage: null };
    }
    const { headerImage: _omit, ...rest } = data;
    return rest;
  };

  const onSuccess = () => {
    notify("Briefing mis à jour", { type: "success" });
    redirect(false);
  };

  return (
    <Edit
      transform={transform}
      mutationOptions={{ onSuccess }}
      mutationMode="pessimistic"
      title="Briefing — informations communes à tous les vols"
      redirect={false}
    >
      <SimpleForm>
        <Typography variant="h6" sx={{ mb: 1 }}>Briefing public</Typography>
        <Alert severity="info" sx={{ mb: 2 }}>
          Ces informations apparaitront sur la page publique reçue par chaque passager
          via le lien <code>{'{{lien_briefing}}'}</code> dans le SMS / email de
          confirmation. Indiquez ici les informations <strong>communes à tous les vols</strong>
          (accès au hangar, tenue, poids max, instructions à l'arrivée, etc.).
        </Alert>

        <Box width="100%" mb={2}>
          <FileInput
            source="headerImage"
            format={formatMediaObject}
            label="Image d'en-tête"
            accept={{ "image/jpeg": [".jpg", ".jpeg"], "image/png": [".png"] }}
            multiple={ false }
            placeholder="Glisser une image (bandeau de la page publique)"
            helperText="JPEG ou PNG • Format paysage recommandé (1600 × 600 px, ratio 16:9 ou 21:9) • Pour remplacer : glissez une nouvelle image puis enregistrez. Pour supprimer : cliquez sur la croix de l'aperçu."
          >
            <BriefingFileField/>
          </FileInput>
        </Box>

        <RichTextInput
          source="html"
          label="Contenu du briefing général"
          fullWidth
          helperText="Apparait dans le premier onglet de la page publique. Pensez à mentionner : accès au hangar, conditions d'arrivée, tenue conseillée, poids max, contact en cas d'imprévu, etc."
        />

        <Box display="flex" gap={2} flexWrap="wrap" width="100%" mt={2}>
          <Box flex={1} minWidth={250}>
            <BooleanInput
              source="showMap"
              label="Afficher le lien Google Maps"
              defaultValue={ true }
              helperText="Lien vers les coordonnées GPS de l'établissement"
            />
          </Box>
        </Box>

        <TextInput
          source="extraContacts"
          label="Contacts complémentaires (optionnel)"
          multiline
          rows={3}
          fullWidth
          helperText="Numéros / adresses additionnels à afficher (en plus du téléphone de l'établissement)"
        />
      </SimpleForm>
    </Edit>
  );
};
