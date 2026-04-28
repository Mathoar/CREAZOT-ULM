import { Edit, SimpleForm, TextInput, NumberInput, BooleanInput, useRecordContext, FileInput, DateInput } from "react-admin";
import { useClient } from "../ClientProvider";
import { Box, Typography, Divider } from '@mui/material';
import { clientWithMicrotrakTags, syncOdooDocuments } from "../../../app/lib/client";
import { useSessionContext } from "../SessionContextProvider";
import { getFormattedValueForBackEnd, isDefined, isDefinedAndNotVoid } from "../../../app/lib/utils";
import { MyFileField } from "../shared/OdooDocumentField";
import { useWatch } from "react-hook-form";

const ParachuteFields = () => {
  const hasParachute = useWatch({ name: "hasParachute" });
  if (!hasParachute) return null;
  return (
    <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
      <Box flex={1}>
        <DateInput source="dateReconditionnementParachute" label="Dernier reconditionnement" fullWidth />
      </Box>
      <Box flex={1}>
        <NumberInput source="periodiciteParachuteMois" label="Périodicité (mois)" min={1} fullWidth
          helperText="Durée constructeur entre deux reconditionnements" />
      </Box>
    </Box>
  );
};

export const AeronefsEdit = () => {

  const { client } = useClient();
  const { session } = useSessionContext();

  const MicrotrakInput = () => {
    return !clientWithMicrotrakTags(client) ? null : 
      <TextInput source="codeBalise" label="Code balise" fullWidth />
  };

  const transform = async ({documents, createdBy, updatedBy, ...data}) => {
      const documentIds = isDefinedAndNotVoid(documents) ? await syncOdooDocuments(documents.map(d => d?.['@id'] ? d : {...d, description: d.title}), 'aeronef', data.id, session) : [];
      return {
        ...data, 
        documents: documentIds,
        createdBy: getFormattedValueForBackEnd(createdBy),
        updatedBy: getFormattedValueForBackEnd(updatedBy),
      };
  };

  return (
    <Edit transform={ transform } redirect="list">
        <SimpleForm>
          <Box display="flex" gap={2} width="100%">
            <Box flex={1}>
              <TextInput source="immatriculationComplete" label="Immatriculation (ex: F-JXYZ)" fullWidth />
            </Box>
            <Box flex={1}>
              <TextInput source="immatriculation" label="Identifiant radio (6 car.)" fullWidth />
            </Box>
          </Box>
          <TextInput source="modele" label="Modèle (ex: Pipistrel Virus SW)" fullWidth />
          <NumberInput source="horametre" label="Horamètre actuel" fullWidth />
          <Box display="flex" gap={2} width="100%">
            <Box flex={1}>
              <NumberInput source="entretien" label="Prochain entretien" fullWidth />
            </Box>
            <Box flex={1}>
              <NumberInput source="changementMoteur" label="Changement du moteur" fullWidth />
            </Box>
          </Box>
          <Box display="flex" gap={2} width="100%">
            <Box flex={1}>
              <NumberInput source="seuilAlerte" label="Seuil d'alerte (en h) avant entretien" fullWidth />
            </Box>
            <Box flex={1}>
              <NumberInput source="seuilAlerteChangementMoteur" label="Seuil d'alerte (en h) avant changement moteur" fullWidth />
            </Box>
          </Box>
          <Box display="flex" gap={2} width="100%">
            <Box flex={1}>
              <TextInput source="typeBalise" label="Type de balise" fullWidth
                helperText="Ex: Microtrak, Flarm, SafeSky..." />
            </Box>
            <Box flex={1}>
              <MicrotrakInput/>
            </Box>
          </Box>
          <Divider sx={{ width: '100%', my: 1 }} />
          <Typography variant="subtitle2" sx={{ mb: 1 }}>Parachute de récupération</Typography>
          <BooleanInput source="hasParachute" label="Équipé d'un parachute" defaultValue={false} />
          <ParachuteFields />
          <Divider sx={{ width: '100%', my: 1 }} />
          <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
              <Box flex={1} display="flex" alignItems="center">
                  <BooleanInput source="decimal" label="Horamètre décimal" defaultValue={ false }/>
              </Box>
              <Box flex={1}>
                  <BooleanInput source="isAvailable" label="Disponible" defaultValue={ false }/>
              </Box>
          </Box>
          <FileInput source="documents" multiple={ true } label="Documents associés">
              <MyFileField source="contentUrl"/>
          </FileInput>
        </SimpleForm>
    </Edit>
  )
};
