import { SimpleForm, TextInput, NumberInput, BooleanInput, required, useRecordContext, FileInput, Create, DateInput } from "react-admin";
import { Box, Typography, Divider } from '@mui/material';
import { useClient } from '../../admin/ClientProvider';
import { clientWithMicrotrakTags, syncOdooDocuments } from "../../../app/lib/client";
import { isDefined, isDefinedAndNotVoid } from "../../../app/lib/utils";
import { useSessionContext } from "../SessionContextProvider";
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

export const AeronefsCreate = () => {

  const { client } = useClient();
  const { session } = useSessionContext();

  const MicrotrakInput = () => {
    return !clientWithMicrotrakTags(client) ? null : 
      <TextInput source="codeBalise" label="Code Microtrak"/>
  };

  const transform = async ({documents, ...data}) => {
      const documentIds = isDefinedAndNotVoid(documents) ? await syncOdooDocuments(documents.map(d => d?.['@id'] ? d : {...d, description: d.title}), 'aeronef', null, session) : [];
      return {...data, documents: documentIds};
  };

  return (
    <Create transform={ transform } redirect="list">
      <SimpleForm>
        <TextInput source="immatriculationComplete" label="Immatriculation (ex: F-JXYZ)" />
        <TextInput source="immatriculation" label="Identifiant radio (6 car.)" validate={required()}/>
        <TextInput source="modele" label="Modèle (ex: Pipistrel Virus SW)" />
        <NumberInput source="horametre" label="Horamètre actuel" validate={required()}/>
        <NumberInput source="entretien" label="Prochain entretien" validate={required()}/>
        <NumberInput source="changementMoteur" label="Changement du moteur" />
        <NumberInput source="seuilAlerte" label="Seuil d'alerte (en h) avant entretien" defaultValue={ 10 } validate={required()}/>
        <NumberInput source="seuilAlerteChangementMoteur" label="Seuil d'alerte (en h) avant changement du moteur" defaultValue={ 200 }/>
        <MicrotrakInput/>
        <TextInput source="typeBalise" label="Type de balise / dispositif de visibilité"
          helperText="Ex: Microtrak, Flarm, SafeSky..." />
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
        { false && <BooleanInput source="alerteEnvoyee" label="Alerte envoyée" defaultValue={ false } /> }
        { false && <BooleanInput source="alerteMoteurEnvoyee" label="Alerte moteur envoyée" defaultValue={ false } /> }
      </SimpleForm>
    </Create>
  );
};
