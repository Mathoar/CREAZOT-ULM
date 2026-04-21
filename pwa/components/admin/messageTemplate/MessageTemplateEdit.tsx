import { Edit, SimpleForm, TextInput } from "react-admin";
import { Typography } from "@mui/material";
import { SmsLiveCounterField } from "../sms/SmsLiveCounter";

export const MessageTemplateEdit = () => (
  <Edit>
    <SimpleForm>
      <TextInput source="title" label="Titre du modèle" fullWidth />
      <TextInput source="body" label="Corps du message" multiline rows={6} fullWidth />
      <SmsLiveCounterField
        source="body"
        resolveVariables
        helperText="Estimation calculée en remplaçant les variables par des valeurs typiques (le résultat final pourra varier selon les réservations)."
      />
      <Typography variant="caption" color="text.secondary" sx={{ mt: 1 }}>
        Variables disponibles : {"{{nom}}"}, {"{{circuit}}"}, {"{{date}}"}, {"{{heure}}"}, {"{{pilote}}"}, {"{{code}}"}, {"{{structure}}"}, {"{{telephone}}"}, {"{{email}}"}, {"{{nb_personnes}}"}, {"{{lien_briefing}}"} (lien vers la page publique de briefing — uniquement si module Planification actif)
      </Typography>
    </SimpleForm>
  </Edit>
);
