import { Edit, SelectInput} from "react-admin";
import { DateTimeInput, ReferenceInput, TabbedForm, TextInput, NumberInput, BooleanInput, ArrayInput, SimpleFormIterator, required, FileInput } from "react-admin";
import { useClient } from '../../admin/ClientProvider';
import { isDefinedAndNotVoid } from "../../../app/lib/utils";
import { clientWithLandingManagement, clientWithOptions, clientWithWebshop, clientWithPlanification, syncDocument } from "../../../app/lib/client";
import { Box, Typography } from "@mui/material";
import { RichTextInput } from "ra-input-rich-text";
import { useSessionContext } from "../SessionContextProvider";
import { BriefingFileField, formatMediaObject } from "../shared/BriefingFileField";
import TvaSelectInput from "../shared/TvaSelectInput";

export const CircuitsEdit = () => {

  const { client } = useClient();
  const { session } = useSessionContext();

  const OptionsInput = () => {
    return !clientWithOptions(client) ? null :
      <BooleanInput source="avecOptions" label="Options disponibles" defaultValue={ false }/>
  };

  const IdsInput = () => {
      return !clientWithWebshop(client) ?
        <TextInput source="code" label="Code interne" fullWidth validate={required()}/> :
        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
          <Box flex={1}>
              <TextInput source="code" label="Code interne" fullWidth validate={required()}/>
          </Box>
          <Box flex={1}>
              <TextInput source="webshopId" label="code e-commerce" fullWidth/>
          </Box>
        </Box>
    };

  const LandingsInput = () => {
      return !clientWithLandingManagement(client) ? null :
        <BooleanInput source="requireLandingDeclaration" label="Déclaration atterrissages" defaultValue={ false }/>
  };

  const AddDefaultLandingInput = () => {
    return !clientWithLandingManagement(client) ? null :
      <BooleanInput source="hadDefaultLanding" label="Ajouter un attérrissage par défaut" defaultValue={ false } fullWidth/>
  };

  const transform = async data => {
    data['nature'] = data['nature']?.['@id'] ?? data['nature'];
    data['qualifications'] = isDefinedAndNotVoid(data['qualifications']) ? data['qualifications'].map(qualification => qualification['@id']) : [];
    data['avecOptions'] = clientWithOptions(client) ? data['avecOptions'] : false;
    if (clientWithPlanification(client)) {
      const img = data['briefingImage'];
      if (img?.rawFile) {
        data['briefingImage'] = await syncDocument(img, session);
      } else if (img?.['@id']) {
        data['briefingImage'] = img['@id'];
      } else if (img === null) {
        data['briefingImage'] = null;
      } else {
        delete data['briefingImage'];
      }
    } else {
      delete data['briefingImage'];
      delete data['briefingHtml'];
    }
    return data;
  };

  return (
  <Edit transform={transform}>
    <TabbedForm syncWithLocation={false}>
      <TabbedForm.Tab label="Paramètres">
        <TextInput source="nom" validate={required()} fullWidth/>
        <IdsInput />
        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
            <Box flex={1} display="flex" alignItems="center">
                <BooleanInput source="prixFixe" label="Prix fixe" defaultValue={ false } fullWidth helperText="Facturation à la minute si non coché"/>
            </Box>
            <Box flex={2}>
                <DateTimeInput source="duree" validate={required()}/>
            </Box>
        </Box>
        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
          <Box flex={1}>
              <NumberInput source="prix" label="Prix TTC (€)" defaultValue={ 0 } validate={required()}/>
          </Box>
          <Box flex={1}>
              <NumberInput source="cout" label="Coût pilote (€)" defaultValue={ 0 } validate={required()}/>
          </Box>
          <Box flex={1}>
              <TvaSelectInput source="tauxTva" label="TVA" />
          </Box>
        </Box>
        <ReferenceInput reference="natures" source="nature.@id">
          <SelectInput label="Nature du circuit" validate={required()}/>
        </ReferenceInput>
        <ArrayInput source="qualifications" label="Qualification(s) requise(s) du pilote">
          <SimpleFormIterator inline disableReordering>
            <ReferenceInput reference="qualifications" source="@id" label="Qualifications" />
          </SimpleFormIterator>
        </ArrayInput>
        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
            <Box flex={1} display="flex" alignItems="center">
              <OptionsInput/>
            </Box>
            <Box flex={1}>
              <BooleanInput source="needsEncadrant" label="Encadrant requis" defaultValue={ false } fullWidth/>
            </Box>
        </Box>
        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
            <Box flex={1} display="flex" alignItems="center">
              <LandingsInput/>
            </Box>
            <Box flex={1}>
              <AddDefaultLandingInput/>
            </Box>
        </Box>
      </TabbedForm.Tab>
      { clientWithPlanification(client) &&
        <TabbedForm.Tab label="Briefing commercial">
          <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
            Ces informations apparaitront sur la page publique reçue par le passager (lien {'{{lien_briefing}}'}). Spécifique à ce circuit.
          </Typography>
          <FileInput source="briefingImage" format={formatMediaObject} label="Image d'illustration" accept={{ "image/jpeg": [".jpg", ".jpeg"], "image/png": [".png"] }} multiple={ false } placeholder="Glisser une image ou cliquer pour sélectionner" helperText="JPEG ou PNG • Format paysage recommandé (1600 × 600 px, ratio 16:9 ou 21:9) • Pour remplacer : glissez une nouvelle image puis enregistrez. Pour supprimer : cliquez sur la croix de l'aperçu.">
            <BriefingFileField/>
          </FileInput>
          <RichTextInput source="briefingHtml" label="Texte du briefing circuit" fullWidth helperText="Description spécifique au circuit (déroulé, points remarquables, etc.)"/>
        </TabbedForm.Tab>
      }
    </TabbedForm>
  </Edit>
  )
};
