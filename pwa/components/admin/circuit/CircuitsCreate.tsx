import { DateTimeInput, ReferenceInput, TabbedForm, TextInput, NumberInput, BooleanInput, ArrayInput, SimpleFormIterator, required, SelectInput, FileInput} from "react-admin";
import { Create } from "react-admin";
import { useWatch } from "react-hook-form";
import { useClient } from '../../admin/ClientProvider';
import { clientWithLandingManagement, clientWithOptions, clientWithWebshop, clientWithPlanification, syncDocument } from "../../../app/lib/client";
import { Box, Typography } from "@mui/material";
import { getFormattedValueForBackEnd, isDefined, isDefinedAndNotVoid } from "../../../app/lib/utils";
import { RichTextInput } from "ra-input-rich-text";
import { useSessionContext } from "../SessionContextProvider";
import { BriefingFileField } from "../shared/BriefingFileField";
import TvaSelectInput from "../shared/TvaSelectInput";

export const CircuitsCreate = () => {

  const { client } = useClient();
  const { session } = useSessionContext();

  const DurationInput = () => {
      const prixFixe = useWatch<{ prixFixe: string }>({ name: "prixFixe" });
      return <DateTimeInput source="duree" defaultValue={ new Date((new Date()).setHours(1,0,0)) } readOnly={ !prixFixe } validate={required()}/>
  };

  const OptionsInput = () => {
    return !clientWithOptions(client) ? null :
      <BooleanInput source="avecOptions" label="Options disponibles" defaultValue={ false } fullWidth/>
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
      <BooleanInput source="requireLandingDeclaration" label="Déclaration atterrissages" defaultValue={ false } fullWidth/>
  };

  const AddDefaultLandingInput = () => {
    return !clientWithLandingManagement(client) ? null :
      <BooleanInput source="hadDefaultLanding" label="Ajouter un attérrissage par défaut" defaultValue={ false } fullWidth/>
  };

  const transform = async ({ qualifications, briefingImage, ...data }) => {
    const briefingImageIri = clientWithPlanification(client)
      ? await syncDocument(briefingImage, session)
      : null;
    return {
      ...data,
      qualifications: isDefinedAndNotVoid(qualifications) ? qualifications.map(q => getFormattedValueForBackEnd(q)) : [],
      ...(clientWithPlanification(client) ? { briefingImage: briefingImageIri } : {}),
    };
  };

  return (
      <Create transform={ transform } redirect="list">
        <TabbedForm syncWithLocation={false}>
          <TabbedForm.Tab label="Paramètres">
            <TextInput source="nom" validate={required()} fullWidth/>
            <IdsInput />
            <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                <Box flex={1} display="flex" alignItems="center">
                    <BooleanInput source="prixFixe" label="Prix fixe" defaultValue={ false } fullWidth helperText="Facturation à la minute si non coché"/>
                </Box>
                <Box flex={2}>
                    <DurationInput />
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
                  <TvaSelectInput source="tauxTva" label="TVA" isCreate />
              </Box>
            </Box>
            <ReferenceInput reference="natures" source="nature">
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
            <BooleanInput source="isAvailable" label="Circuit disponible (réservable)" defaultValue={true} helperText="Décocher pour masquer ce circuit des formulaires de réservation et de prestation" />
          </TabbedForm.Tab>
          { clientWithPlanification(client) &&
            <TabbedForm.Tab label="Briefing commercial">
              <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                Ces informations apparaitront sur la page publique reçue par le passager (lien {'{{lien_briefing}}'}). Spécifique à ce circuit.
              </Typography>
              <FileInput source="briefingImage" label="Image d'illustration" accept={{ "image/jpeg": [".jpg", ".jpeg"], "image/png": [".png"] }} multiple={ false } placeholder="Glisser une image ou cliquer pour sélectionner" helperText="JPEG ou PNG • Format paysage recommandé (1600 × 600 px, ratio 16:9 ou 21:9) • Les images > 1920 px sont automatiquement redimensionnées.">
                <BriefingFileField/>
              </FileInput>
              <RichTextInput source="briefingHtml" label="Texte du briefing circuit" fullWidth helperText="Description spécifique au circuit (déroulé, points remarquables, etc.)"/>
            </TabbedForm.Tab>
          }
        </TabbedForm>
      </Create>
  )
};
