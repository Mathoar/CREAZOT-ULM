import { Edit, SimpleForm, TextInput, BooleanInput } from "react-admin";
import { Typography, Box } from "@mui/material";
import SmsIcon from "@mui/icons-material/Sms";
import EmailIcon from "@mui/icons-material/Email";
import { useWatch } from "react-hook-form";
import { SmsLiveCounterField } from "../sms/SmsLiveCounter";
import { useClient } from "../ClientProvider";

const ChannelHelper = () => {
  const isSms = useWatch({ name: "isSmsMessage" });
  return (
    <Box display="flex" alignItems="center" gap={1} sx={{ mt: -1, mb: 1 }}>
      {isSms ? <SmsIcon fontSize="small" color="primary" /> : <EmailIcon fontSize="small" color="primary" />}
      <Typography variant="caption" color="text.secondary">
        {isSms
          ? "Modèle réservé aux envois SMS — pensez à rester court (≤ 160 caractères pour 1 SMS)."
          : "Modèle réservé aux envois Email — vous pouvez être plus détaillé."}
      </Typography>
    </Box>
  );
};

const ConditionalSmsCounter = () => {
  const isSms = useWatch({ name: "isSmsMessage" });
  if (!isSms) return null;
  return (
    <SmsLiveCounterField
      source="body"
      resolveVariables
      helperText="Estimation calculée en remplaçant les variables par des valeurs typiques (le résultat final pourra varier selon les réservations)."
    />
  );
};

export const MessageTemplateEdit = () => {
  const { client } = useClient();
  const hasSms = client?.hasSMS === true;

  return (
    <Edit>
      <SimpleForm>
        <TextInput source="title" label="Titre du modèle" fullWidth />
        {hasSms ? (
          <BooleanInput
            source="isSmsMessage"
            label="Modèle SMS (sinon Email)"
            helperText="Ce modèle ne sera proposé que pour le canal correspondant sur la page Planification."
          />
        ) : (
          <Typography variant="caption" color="text.secondary" sx={{ mb: 1 }}>
            Module SMS désactivé pour ce club — tous les modèles sont des modèles Email.
          </Typography>
        )}
        <ChannelHelper />
        <TextInput source="body" label="Corps du message" multiline rows={6} fullWidth />
        <ConditionalSmsCounter />
        <Typography variant="caption" color="text.secondary" sx={{ mt: 1 }}>
          Variables disponibles : {"{{nom}}"}, {"{{circuit}}"}, {"{{date}}"}, {"{{heure}}"}, {"{{pilote}}"}, {"{{code}}"}, {"{{enseigne}}"}, {"{{structure}}"}, {"{{telephone}}"}, {"{{email}}"}, {"{{nb_personnes}}"}, {"{{lien_briefing}}"} (lien vers la page publique de briefing — uniquement si module Planification actif)
        </Typography>
      </SimpleForm>
    </Edit>
  );
};
