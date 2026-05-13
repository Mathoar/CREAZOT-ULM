import { useState, useEffect, useCallback, useMemo } from "react";
import {
  Box, Typography, Paper, Table, TableHead, TableRow, TableCell,
  TableBody, Checkbox, TextField as MuiTextField, Button, Chip,
  MenuItem, Select, FormControl, InputLabel, Alert, CircularProgress,
  IconButton, Tooltip,
} from "@mui/material";
import SendIcon from "@mui/icons-material/Send";
import SmsIcon from "@mui/icons-material/Sms";
import EmailIcon from "@mui/icons-material/Email";
import TranslateIcon from "@mui/icons-material/Translate";
import RestartAltIcon from "@mui/icons-material/RestartAlt";
import { Title, useDataProvider, useNotify } from "react-admin";
import { useClient } from "../ClientProvider";
import { useSessionContext } from "../SessionContextProvider";
import { SmsLiveCounter } from "../sms/SmsLiveCounter";

const SUPPORTED_LANGUAGES: { code: string; label: string; flag: string }[] = [
  { code: "fr", label: "Français", flag: "🇫🇷" },
  { code: "en", label: "English", flag: "🇬🇧" },
  { code: "es", label: "Español", flag: "🇪🇸" },
  { code: "de", label: "Deutsch", flag: "🇩🇪" },
  { code: "it", label: "Italiano", flag: "🇮🇹" },
];

const API_DOMAIN = process.env.NEXT_PUBLIC_ENTRYPOINT || "";

interface Reservation {
  "@id": string;
  id: number;
  nom: string;
  telephone: string;
  email: string;
  code: string;
  debut: string;
  circuit?: { nom: string; "@id": string };
  pilote?: { firstName: string; lastName: string };
  notificationSent?: boolean;
  notificationReceived?: boolean;
  selectedOptions?: Array<{ nom: string }>;
}

interface MessageTemplate {
  "@id": string;
  id: number;
  title: string;
  body: string;
  isSmsMessage?: boolean;
}

interface ReservationGroup {
  code: string;
  reservations: Reservation[];
  selected: boolean;
}

export const PlanningPage = () => {
  const { client } = useClient();
  const { session } = useSessionContext();
  const dataProvider = useDataProvider();
  const notify = useNotify();

  const [date, setDate] = useState(() => new Date().toISOString().slice(0, 10));
  const [groups, setGroups] = useState<ReservationGroup[]>([]);
  const [templates, setTemplates] = useState<MessageTemplate[]>([]);
  const [selectedTemplateId, setSelectedTemplateId] = useState("");
  const hasSms = client?.hasSMS === true;
  const hasAi = client?.hasAI === true;
  const [method, setMethod] = useState<"sms" | "email">(hasSms ? "sms" : "email");
  const [messageBody, setMessageBody] = useState("");
  const [originalBody, setOriginalBody] = useState("");
  const [language, setLanguage] = useState<string>("fr");
  const [translating, setTranslating] = useState(false);
  const [loading, setLoading] = useState(false);
  const [sending, setSending] = useState(false);
  const [result, setResult] = useState<{ sent: number; failed: number; errors: string[] } | null>(null);

  const filteredTemplates = useMemo(() => {
    return templates.filter((t) => (method === "sms" ? t.isSmsMessage === true : t.isSmsMessage !== true));
  }, [templates, method]);

  const fetchReservations = useCallback(async () => {
    if (!client?.id) return;
    setLoading(true);
    try {
      const startOfDay = `${date}T00:00:00`;
      const endOfDay = `${date}T23:59:59`;

      const { data } = await dataProvider.getList("reservations", {
        pagination: { page: 1, perPage: 200 },
        sort: { field: "debut", order: "ASC" },
        filter: {
          "debut[after]": startOfDay,
          "debut[before]": endOfDay,
        },
      });

      const grouped: Record<string, Reservation[]> = {};
      for (const r of data as Reservation[]) {
        const code = r.code || `solo-${r.id}`;
        if (!grouped[code]) grouped[code] = [];
        grouped[code].push(r);
      }

      setGroups(
        Object.entries(grouped).map(([code, reservations]) => ({
          code,
          reservations: reservations.sort((a, b) => new Date(a.debut).getTime() - new Date(b.debut).getTime()),
          selected: false,
        }))
      );
    } catch (e) {
      console.error("Erreur chargement réservations", e);
    } finally {
      setLoading(false);
    }
  }, [client?.id, date, dataProvider]);

  const fetchTemplates = useCallback(async () => {
    if (!client?.id) return;
    try {
      const { data } = await dataProvider.getList("message_templates", {
        pagination: { page: 1, perPage: 50 },
        sort: { field: "title", order: "ASC" },
        filter: {},
      });
      setTemplates(data as MessageTemplate[]);
    } catch (e) {
      console.error("Erreur chargement templates", e);
    }
  }, [client?.id, dataProvider]);

  useEffect(() => {
    fetchReservations();
  }, [fetchReservations]);

  useEffect(() => {
    fetchTemplates();
  }, [fetchTemplates]);

  const toggleGroup = (code: string) => {
    setGroups((prev) =>
      prev.map((g) => (g.code === code ? { ...g, selected: !g.selected } : g))
    );
  };

  const selectAll = () => {
    const allSelected = groups.every((g) => g.selected);
    setGroups((prev) => prev.map((g) => ({ ...g, selected: !allSelected })));
  };

  const handleTemplateChange = (templateId: string) => {
    setSelectedTemplateId(templateId);
    const tpl = templates.find((t) => String(t.id) === templateId);
    if (tpl) {
      setMessageBody(tpl.body);
      setOriginalBody(tpl.body);
      setLanguage("fr");
    } else {
      setOriginalBody("");
    }
  };

  useEffect(() => {
    if (selectedTemplateId) {
      const stillValid = filteredTemplates.some((t) => String(t.id) === selectedTemplateId);
      if (!stillValid) {
        setSelectedTemplateId("");
        setMessageBody("");
        setOriginalBody("");
        setLanguage("fr");
      }
    }
  }, [filteredTemplates, selectedTemplateId]);

  const handleTranslate = async (targetLang: string) => {
    if (!hasAi) return;
    if (targetLang === language) return;

    if (targetLang === "fr") {
      if (originalBody) {
        setMessageBody(originalBody);
        setLanguage("fr");
      }
      return;
    }

    const sourceText = language === "fr" ? messageBody : originalBody;
    if (!sourceText.trim()) {
      notify("Aucun texte à traduire", { type: "warning" });
      return;
    }

    setTranslating(true);
    try {
      const res = await fetch(`${API_DOMAIN}/admin/ai/translate`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${session?.accessToken}`,
          "X-Client-Id": String(client?.id),
        },
        body: JSON.stringify({
          text: sourceText,
          targetLang,
          sourceLang: "fr",
        }),
      });
      const data = await res.json();
      if (data.translation) {
        if (!originalBody) setOriginalBody(messageBody);
        setMessageBody(data.translation);
        setLanguage(targetLang);
        notify(`Traduction en ${SUPPORTED_LANGUAGES.find((l) => l.code === targetLang)?.label} appliquée`, { type: "success" });
      } else {
        notify(data.error || "Erreur de traduction", { type: "error" });
      }
    } catch (e: any) {
      notify("Erreur réseau pendant la traduction : " + e.message, { type: "error" });
    } finally {
      setTranslating(false);
    }
  };

  const restoreOriginal = () => {
    if (originalBody) {
      setMessageBody(originalBody);
      setLanguage("fr");
    }
  };

  const selectedGroups = groups.filter((g) => g.selected);
  const selectedReservationIds = selectedGroups.flatMap((g) =>
    g.reservations.map((r) => {
      const raw = r["@id"] || String(r.id);
      return typeof raw === "string" ? parseInt(raw.replace(/.*\//, ""), 10) : raw;
    })
  );

  const handleSend = async () => {
    if (selectedReservationIds.length === 0) {
      notify("Sélectionnez au moins un groupe de réservations", { type: "warning" });
      return;
    }
    if (!messageBody.trim()) {
      notify("Le message ne peut pas être vide", { type: "warning" });
      return;
    }

    setSending(true);
    setResult(null);

    try {
      const tpl = templates.find((t) => String(t.id) === selectedTemplateId);
      const res = await fetch(`${API_DOMAIN}/admin/notifications/send`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${session?.accessToken}`,
          "X-Client-Id": String(client?.id),
        },
        body: JSON.stringify({
          reservationIds: selectedReservationIds,
          method,
          body: messageBody,
          templateTitle: tpl?.title || "",
          clientId: client?.id,
        }),
      });

      const data = await res.json();

      if (data.success) {
        setResult({ sent: data.sent, failed: data.failed, errors: data.errors || [] });
        notify(`${data.sent} notification(s) envoyée(s)`, { type: "success" });
        fetchReservations();
      } else {
        notify(data.error || "Erreur lors de l'envoi", { type: "error" });
      }
    } catch (e: any) {
      notify("Erreur réseau: " + e.message, { type: "error" });
    } finally {
      setSending(false);
    }
  };

  const formatTime = (dateStr: string) => {
    try {
      return new Date(dateStr).toLocaleTimeString("fr-FR", { hour: "2-digit", minute: "2-digit" });
    } catch {
      return "";
    }
  };

  return (
    <Box p={2}>
      <Title title="Planification" />
      <Typography variant="h5" gutterBottom>
        <SmsIcon sx={{ mr: 1, verticalAlign: "middle" }} />
        Planification des notifications
      </Typography>

      <Box display="flex" gap={2} alignItems="center" mb={3}>
        <MuiTextField
          type="date"
          label="Date"
          value={date}
          onChange={(e) => setDate(e.target.value)}
          InputLabelProps={{ shrink: true }}
          size="small"
        />
        <Button variant="outlined" size="small" onClick={selectAll}>
          {groups.every((g) => g.selected) ? "Désélectionner tout" : "Sélectionner tout"}
        </Button>
      </Box>

      {loading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : groups.length === 0 ? (
        <Alert severity="info">Aucune réservation pour cette date.</Alert>
      ) : (
        <Paper sx={{ mb: 3, overflow: "auto" }}>
          <Table size="small">
            <TableHead>
              <TableRow>
                <TableCell padding="checkbox" />
                <TableCell>Code</TableCell>
                <TableCell>Heure</TableCell>
                <TableCell>Passager(s)</TableCell>
                <TableCell>Circuit</TableCell>
                <TableCell>Téléphone</TableCell>
                <TableCell>Email</TableCell>
                <TableCell>Statut</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {groups.map((group) => (
                <TableRow
                  key={group.code}
                  hover
                  onClick={() => toggleGroup(group.code)}
                  sx={{
                    cursor: "pointer",
                    bgcolor: group.selected ? "action.selected" : undefined,
                  }}
                >
                  <TableCell padding="checkbox">
                    <Checkbox checked={group.selected} />
                  </TableCell>
                  <TableCell>
                    <Typography variant="body2" fontWeight="bold">
                      {group.code}
                    </Typography>
                    {group.reservations.length > 1 && (
                      <Chip label={`${group.reservations.length} pers.`} size="small" sx={{ ml: 1 }} />
                    )}
                  </TableCell>
                  <TableCell>{formatTime(group.reservations[0]?.debut)}</TableCell>
                  <TableCell>
                    {group.reservations.map((r) => r.nom).join(", ")}
                  </TableCell>
                  <TableCell>
                    {group.reservations[0]?.circuit?.nom || "—"}
                  </TableCell>
                  <TableCell>
                    {group.reservations[0]?.telephone || "—"}
                  </TableCell>
                  <TableCell>
                    {group.reservations[0]?.email || "—"}
                  </TableCell>
                  <TableCell>
                    {group.reservations.some((r) => r.notificationSent) && (
                      <Chip label="Envoyé" color="success" size="small" sx={{ mr: 0.5 }} />
                    )}
                    {group.reservations.some((r) => r.notificationReceived) && (
                      <Chip label="Reçu" color="info" size="small" />
                    )}
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </Paper>
      )}

      <Paper sx={{ p: 2 }}>
        <Typography variant="h6" gutterBottom>
          Composer le message
        </Typography>

        <Box display="flex" gap={2} mb={2} flexWrap="wrap" alignItems="center">
          <FormControl size="small" sx={{ minWidth: 150 }}>
            <InputLabel>Méthode</InputLabel>
            <Select
              value={method}
              label="Méthode"
              onChange={(e) => setMethod(e.target.value as "sms" | "email")}
            >
              {hasSms && (
                <MenuItem value="sms">
                  <Box display="flex" alignItems="center" gap={1}>
                    <SmsIcon fontSize="small" /> SMS
                  </Box>
                </MenuItem>
              )}
              <MenuItem value="email">
                <Box display="flex" alignItems="center" gap={1}>
                  <EmailIcon fontSize="small" /> Email
                </Box>
              </MenuItem>
            </Select>
          </FormControl>

          <FormControl size="small" sx={{ minWidth: 280 }}>
            <InputLabel>Modèle de message ({method === "sms" ? "SMS" : "Email"})</InputLabel>
            <Select
              value={selectedTemplateId}
              label={`Modèle de message (${method === "sms" ? "SMS" : "Email"})`}
              onChange={(e) => handleTemplateChange(e.target.value)}
            >
              <MenuItem value="">
                <em>— Message libre —</em>
              </MenuItem>
              {filteredTemplates.length === 0 ? (
                <MenuItem value="" disabled>
                  <em>Aucun modèle {method === "sms" ? "SMS" : "Email"} disponible</em>
                </MenuItem>
              ) : (
                filteredTemplates.map((t) => (
                  <MenuItem key={t.id} value={String(t.id)}>
                    {t.title}
                  </MenuItem>
                ))
              )}
            </Select>
          </FormControl>

          {hasAi && (
            <>
              <FormControl size="small" sx={{ minWidth: 180 }}>
                <InputLabel>Langue d'envoi</InputLabel>
                <Select
                  value={language}
                  label="Langue d'envoi"
                  onChange={(e) => handleTranslate(e.target.value)}
                  disabled={translating || !messageBody.trim()}
                  startAdornment={translating ? <CircularProgress size={16} sx={{ mr: 1 }} /> : null}
                >
                  {SUPPORTED_LANGUAGES.map((lang) => (
                    <MenuItem key={lang.code} value={lang.code}>
                      <Box display="flex" alignItems="center" gap={1}>
                        <span>{lang.flag}</span> {lang.label}
                      </Box>
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>

              {language !== "fr" && originalBody && (
                <Tooltip title="Restaurer le texte d'origine en français">
                  <IconButton size="small" onClick={restoreOriginal} disabled={translating}>
                    <RestartAltIcon fontSize="small" />
                  </IconButton>
                </Tooltip>
              )}
            </>
          )}
        </Box>

        {hasAi && language !== "fr" && (
          <Alert severity="info" icon={<TranslateIcon />} sx={{ mb: 2 }}>
            Texte traduit en <strong>{SUPPORTED_LANGUAGES.find((l) => l.code === language)?.label}</strong> par IA.
            Vérifie le contenu et modifie-le librement avant l'envoi — les variables <code>{"{{...}}"}</code> sont préservées et résolues par le serveur au moment de l'envoi.
          </Alert>
        )}

        <MuiTextField
          multiline
          rows={5}
          fullWidth
          label="Message à envoyer"
          value={messageBody}
          onChange={(e) => setMessageBody(e.target.value)}
          placeholder="Bonjour {{nom}}, votre vol {{circuit}} est confirmé le {{date}} à {{heure}}. Pilote : {{pilote}}. — {{enseigne}}"
          sx={{ mb: 1 }}
        />

        <Typography variant="caption" color="text.secondary" display="block" mb={2}>
          Variables : {"{{nom}}"}, {"{{circuit}}"}, {"{{date}}"}, {"{{heure}}"}, {"{{pilote}}"}, {"{{code}}"}, {"{{enseigne}}"}, {"{{structure}}"}, {"{{telephone}}"}, {"{{email}}"}, {"{{nb_personnes}}"}, {"{{lien_briefing}}"}
        </Typography>

        {method === "sms" && (
          <SmsLiveCounter
            body={messageBody}
            multiplier={selectedGroups.length}
            multiplierLabel="envoi"
          />
        )}

        {result && (
          <Alert severity={result.failed > 0 ? "warning" : "success"} sx={{ mb: 2 }}>
            {result.sent} envoyé(s), {result.failed} échec(s).
            {result.errors.length > 0 && (
              <ul style={{ margin: 0, paddingLeft: 16 }}>
                {result.errors.map((err, i) => (
                  <li key={i}>{err}</li>
                ))}
              </ul>
            )}
          </Alert>
        )}

        <Button
          variant="contained"
          color="primary"
          startIcon={sending ? <CircularProgress size={20} color="inherit" /> : <SendIcon />}
          onClick={handleSend}
          disabled={sending || selectedReservationIds.length === 0 || !messageBody.trim()}
          size="large"
        >
          {sending
            ? "Envoi en cours..."
            : `Envoyer ${method === "sms" ? "SMS" : "Email"} (${selectedGroups.length} groupe${selectedGroups.length > 1 ? "s" : ""})`}
        </Button>
      </Paper>
    </Box>
  );
};
