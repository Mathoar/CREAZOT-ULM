import { useState } from "react";
import {
  Edit,
  SimpleForm,
  TextInput,
  SelectInput,
  DateTimeInput,
  ReferenceInput,
  AutocompleteInput,
  required,
  useGetList,
  FunctionField,
  useRecordContext,
  useNotify,
  useRefresh,
} from "react-admin";
import { RichTextInput } from "ra-input-rich-text";
import {
  Box, Typography, Divider, Alert, Chip,
  Button, Dialog, DialogTitle, DialogContent, DialogActions, TextField,
  MenuItem, Select, InputLabel, FormControl,
} from "@mui/material";
import SendIcon from "@mui/icons-material/Send";
import { useSessionContext } from "../SessionContextProvider";
import { useClient } from "../ClientProvider";

const typeChoices = [
  { id: "incident", name: "Incident" },
  { id: "accident", name: "Accident" },
  { id: "quasi_accident", name: "Quasi-accident" },
  { id: "observation", name: "Observation" },
  { id: "note_interne", name: "Note interne" },
];

const statusChoices = [
  { id: "ouvert", name: "Ouvert" },
  { id: "en_cours", name: "En cours de traitement" },
  { id: "clos", name: "Clos" },
];

const recipientChoices = [
  { id: "dsac", label: "DSAC / DGAC" },
  { id: "bea", label: "BEA" },
  { id: "autre", label: "Autre" },
];

const RegulatoryReminder = () => {
  const record = useRecordContext();
  const { data } = useGetList("site-settings", { pagination: { page: 1, perPage: 1 } });
  const settings = data?.[0];
  const delaiDGAC = settings?.delaiNotificationDGACHeures ?? 72;
  const delaiCR = settings?.delaiCompteRenduSuiviJours ?? 30;

  if (record?.type === "note_interne") return null;

  return (
    <Alert severity="info" sx={{ width: "100%", mb: 2 }}>
      <strong>Rappel réglementaire :</strong> notification à l'exploitant sous {delaiDGAC}h,
      compte-rendu de suivi sous {delaiCR} jours avec mesures correctives.
      En cas d'accident, notification au BEA obligatoire.
    </Alert>
  );
};

const API_DOMAIN = process.env.NEXT_PUBLIC_ENTRYPOINT || "";

const SendReportButton = () => {
  const record = useRecordContext();
  const notify = useNotify();
  const refresh = useRefresh();
  const { session } = useSessionContext();
  const { client } = useClient();
  const [open, setOpen] = useState(false);
  const [email, setEmail] = useState("");
  const [recipientType, setRecipientType] = useState("dsac");
  const [sending, setSending] = useState(false);

  if (!record?.compteRenduSuivi) return null;

  const handleSend = async () => {
    if (!email) {
      notify("Veuillez saisir une adresse email", { type: "warning" });
      return;
    }
    setSending(true);
    try {
      const headers: Record<string, string> = {
        "Content-Type": "application/json",
        Authorization: `Bearer ${session?.accessToken}`,
      };
      if (client?.id) headers["X-Client-Id"] = String(client.id);

      const res = await fetch(`${API_DOMAIN}/admin/security-event/send-report`, {
        method: "POST",
        headers,
        body: JSON.stringify({
          securityEventId: record.id,
          recipientEmail: email,
          recipientType,
        }),
      });
      if (!res.ok) throw new Error(await res.text());

      const destLabel = recipientChoices.find((c) => c.id === recipientType)?.label ?? "destinataire";
      notify(`Compte-rendu envoyé à ${destLabel}`, { type: "success" });
      setOpen(false);
      setEmail("");
      refresh();
    } catch (e: any) {
      notify(`Erreur : ${e.message}`, { type: "error" });
    } finally {
      setSending(false);
    }
  };

  return (
    <>
      <Button
        variant="outlined"
        color="primary"
        startIcon={<SendIcon />}
        onClick={() => setOpen(true)}
        sx={{ mt: 1 }}
      >
        Envoyer le CR
      </Button>
      <Dialog open={open} onClose={() => setOpen(false)} maxWidth="sm" fullWidth>
        <DialogTitle>Envoi du compte-rendu</DialogTitle>
        <DialogContent>
          <Typography variant="body2" sx={{ mb: 2 }}>
            Le compte-rendu sera envoyé en pièce jointe PDF à l'adresse indiquée.
          </Typography>
          <FormControl fullWidth sx={{ mb: 2, mt: 1 }}>
            <InputLabel>Destinataire</InputLabel>
            <Select
              value={recipientType}
              onChange={(e) => setRecipientType(e.target.value)}
              label="Destinataire"
            >
              {recipientChoices.map((c) => (
                <MenuItem key={c.id} value={c.id}>{c.label}</MenuItem>
              ))}
            </Select>
          </FormControl>
          <TextField
            label="Adresse email"
            type="email"
            fullWidth
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder={
              recipientType === "dsac" ? "dsac-xx@aviation-civile.gouv.fr"
              : recipientType === "bea" ? "bea@bea.aero"
              : "destinataire@exemple.com"
            }
          />
          {recipientType !== "autre" && (
            <Typography variant="caption" color="text.secondary" sx={{ mt: 1, display: "block" }}>
              La date de notification {recipientType === "dsac" ? "DGAC" : "BEA"} sera automatiquement renseignée.
            </Typography>
          )}
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setOpen(false)}>Annuler</Button>
          <Button onClick={handleSend} variant="contained" disabled={sending}>
            {sending ? "Envoi en cours…" : "Envoyer"}
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export const SecurityEventEdit = () => (
  <Edit mutationMode="pessimistic">
    <SimpleForm>
      <RegulatoryReminder />

      <Box display="flex" gap={2} width="100%">
        <Box flex={1}>
          <SelectInput source="type" label="Type" choices={typeChoices} validate={required()} fullWidth />
        </Box>
        <Box flex={1}>
          <SelectInput source="status" label="Statut" choices={statusChoices} validate={required()} fullWidth />
        </Box>
      </Box>

      <DateTimeInput source="dateEvenement" label="Date de l'événement" validate={required()} fullWidth />

      <ReferenceInput source="pilote" reference="users" sort={{ field: "lastName", order: "ASC" }}>
        <AutocompleteInput
          optionText={(r: any) => r ? `${r.firstName ?? ""} ${r.lastName ?? ""}` : ""}
          label="Pilote concerné"
          validate={required()}
        />
      </ReferenceInput>

      <ReferenceInput source="aeronef" reference="aeronefs" sort={{ field: "immatriculation", order: "ASC" }}>
        <AutocompleteInput optionText="immatriculation" label="Aéronef" />
      </ReferenceInput>

      <TextInput source="description" label="Description" multiline rows={4} fullWidth validate={required()} />

      <Divider sx={{ width: "100%", my: 2 }} />
      <Typography variant="subtitle1" fontWeight={600}>Suivi des notifications</Typography>

      <Box display="flex" gap={2} width="100%">
        <Box flex={1}>
          <DateTimeInput source="dateNotificationExploitant" label="Notification exploitant" fullWidth />
        </Box>
        <Box flex={1}>
          <DateTimeInput source="dateNotificationDGAC" label="Notification DGAC" fullWidth />
        </Box>
        <Box flex={1}>
          <DateTimeInput source="dateNotificationBEA" label="Notification BEA" fullWidth />
        </Box>
      </Box>

      <Divider sx={{ width: "100%", my: 2 }} />
      <Typography variant="subtitle1" fontWeight={600}>Compte-rendu de suivi</Typography>

      <RichTextInput source="compteRenduSuivi" label="" fullWidth />

      <Box display="flex" gap={2} alignItems="center" width="100%">
        <SendReportButton />
        <FunctionField render={(record: any) =>
          record?.dateCloture ? (
            <Chip
              label={`Clôturé le ${new Date(record.dateCloture).toLocaleDateString("fr-FR", { day: "2-digit", month: "long", year: "numeric", hour: "2-digit", minute: "2-digit" })}`}
              color="success"
              variant="outlined"
            />
          ) : null
        } />
      </Box>
    </SimpleForm>
  </Edit>
);
