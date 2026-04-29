import { useState } from "react";
import {
  Show,
  SimpleShowLayout,
  useRecordContext,
  useNotify,
  useRefresh,
} from "react-admin";
import { ProtectedShowActions } from "../PermissionGuards";
import {
  Box,
  Typography,
  Chip,
  Paper,
  Button,
  TextField as MuiTextField,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
  Alert,
  Divider,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
} from "@mui/material";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import CancelIcon from "@mui/icons-material/Cancel";
import SmartToyIcon from "@mui/icons-material/SmartToy";
import PhoneIcon from "@mui/icons-material/Phone";
import EmailIcon from "@mui/icons-material/Email";
import PersonIcon from "@mui/icons-material/Person";
import FlightIcon from "@mui/icons-material/Flight";
import EventIcon from "@mui/icons-material/Event";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import GroupIcon from "@mui/icons-material/Group";
import ArrowBackIcon from "@mui/icons-material/ArrowBack";
import { useSessionContext } from "../SessionContextProvider";
import axios from "axios";
import { ENTRYPOINT } from "../../../config/entrypoint";

const statusConfig: Record<string, { label: string; color: "default" | "warning" | "info" | "primary" | "success" | "error"; description: string }> = {
  pending: { label: "En cours", color: "info", description: "L'appel est en cours ou vient de se terminer" },
  analyzing: { label: "Analyse IA", color: "info", description: "L'IA analyse la demande" },
  proposing: { label: "Créneaux proposés", color: "primary", description: "Des créneaux ont été proposés au client" },
  awaiting_customer: { label: "Attente client", color: "info", description: "En attente de la réponse du client" },
  awaiting_club: { label: "À valider", color: "warning", description: "Le client a choisi un créneau — à vous de confirmer" },
  confirmed: { label: "Confirmée", color: "success", description: "La réservation a été confirmée" },
  cancelled: { label: "Annulée", color: "error", description: "La demande a été refusée ou annulée" },
  expired: { label: "Expirée", color: "default", description: "La demande a expiré sans suite" },
};

const rawId = (record: any): string => {
  const id = record?.id ?? record?.['@id'] ?? '';
  return String(id).replace(/.*\//, '');
};

const channelLabels: Record<string, string> = {
  email: "📧 Email",
  voice: "📞 Appel vocal",
  sms: "💬 SMS",
  whatsapp: "💬 WhatsApp",
};

const InfoCard = ({ icon, label, value }: { icon: React.ReactNode; label: string; value: string | null | undefined }) => {
  if (!value) return null;
  return (
    <Box sx={{ display: "flex", alignItems: "center", gap: 1, py: 0.5 }}>
      <Box sx={{ color: "#6366f1", display: "flex" }}>{icon}</Box>
      <Box>
        <Typography variant="caption" color="text.secondary" sx={{ display: "block", lineHeight: 1.2 }}>
          {label}
        </Typography>
        <Typography variant="body2" sx={{ fontWeight: 600 }}>
          {value}
        </Typography>
      </Box>
    </Box>
  );
};

const ThreadDetail = () => {
  const record = useRecordContext();
  const { session } = useSessionContext();
  const notify = useNotify();
  const refresh = useRefresh();

  const [replyText, setReplyText] = useState("");
  const [replySending, setReplySending] = useState(false);
  const [cancelDialogOpen, setCancelDialogOpen] = useState(false);

  if (!record) return null;

  const ctx = record.aiContext || {};
  const extracted = ctx.extracted || {};
  const proposedSlots = ctx.proposed_slots || [];
  const cfg = statusConfig[record.status] || { label: record.status, color: "default" as const, description: "" };

  const customerName = record.customerName || extracted.customer_name || "—";
  const customerPhone = record.customerPhone || extracted.customer_phone;
  const customerEmail = record.customerEmail;

  const handleValidate = async (slotIndex: number = 0) => {
    try {
      await axios.post(
        `${ENTRYPOINT}/admin/ai-reservation/conversations/${rawId(record)}/validate`,
        { slotIndex },
        { headers: { Authorization: `Bearer ${session?.accessToken}` } }
      );
      notify("Réservation confirmée — notification envoyée au client", { type: "success" });
      refresh();
    } catch (err: any) {
      notify(err?.response?.data?.error || "Erreur", { type: "error" });
    }
  };

  const handleReply = async () => {
    setReplySending(true);
    try {
      await axios.post(
        `${ENTRYPOINT}/admin/ai-reservation/conversations/${rawId(record)}/reply`,
        { message: replyText },
        { headers: { Authorization: `Bearer ${session?.accessToken}` } }
      );
      notify("Réponse envoyée au client", { type: "success" });
      setReplyText("");
      refresh();
    } catch (err: any) {
      notify(err?.response?.data?.error || "Erreur lors de l'envoi", { type: "error" });
    }
    setReplySending(false);
  };

  const handleCancel = async () => {
    try {
      await axios.post(
        `${ENTRYPOINT}/admin/ai-reservation/conversations/${rawId(record)}/cancel`,
        {},
        { headers: { Authorization: `Bearer ${session?.accessToken}` } }
      );
      notify("Demande refusée — notification envoyée au client", { type: "info" });
      setCancelDialogOpen(false);
      refresh();
    } catch (err: any) {
      notify(err?.response?.data?.error || "Erreur", { type: "error" });
    }
  };

  const isActionable = record.status === "awaiting_club";
  const isClosed = record.status === "confirmed" || record.status === "cancelled" || record.status === "expired";

  return (
    <Box sx={{ maxWidth: 900, mx: "auto", p: 2 }}>
      <Button
        startIcon={<ArrowBackIcon />}
        href="#/conversation_threads"
        sx={{ mb: 2, textTransform: "none", color: "#666" }}
      >
        Retour à la liste
      </Button>

      {/* Header */}
      <Paper
        elevation={0}
        sx={{
          p: 3,
          mb: 3,
          borderRadius: 3,
          background: isActionable ? "linear-gradient(135deg, #fff8e1, #fff3e0)" : "#f8f9ff",
          border: isActionable ? "2px solid #ed6c02" : "1px solid #e8eaf6",
        }}
      >
        <Box sx={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", flexWrap: "wrap", gap: 2 }}>
          <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
            <SmartToyIcon sx={{ fontSize: 36, color: isActionable ? "#ed6c02" : "#6366f1" }} />
            <Box>
              <Typography variant="h5" sx={{ fontWeight: 700 }}>
                {customerName}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                {channelLabels[record.channel] || record.channel} — {new Date(record.createdAt).toLocaleString("fr-FR")}
              </Typography>
            </Box>
          </Box>
          <Box sx={{ display: "flex", flexDirection: "column", alignItems: "flex-end", gap: 0.5 }}>
            <Chip label={cfg.label} color={cfg.color} sx={{ fontWeight: 700, fontSize: "0.85rem" }} />
            <Typography variant="caption" color="text.secondary">
              {cfg.description}
            </Typography>
          </Box>
        </Box>
      </Paper>

      {/* Alerte urgente pour awaiting_club */}
      {isActionable && (
        <Alert
          severity="warning"
          sx={{ mb: 3, borderRadius: 2, "& .MuiAlert-icon": { fontSize: 28 } }}
        >
          <Typography variant="subtitle2" sx={{ fontWeight: 700 }}>
            Action requise
          </Typography>
          <Typography variant="body2">
            Ce client attend votre confirmation. Validez le créneau ci-dessous ou refusez la demande.
          </Typography>
        </Alert>
      )}

      <Box sx={{ display: "grid", gridTemplateColumns: { xs: "1fr", md: "1fr 1fr" }, gap: 3, mb: 3 }}>
        {/* Infos client */}
        <Paper elevation={0} sx={{ p: 2.5, borderRadius: 2, border: "1px solid #e0e0e0" }}>
          <Typography variant="subtitle2" sx={{ fontWeight: 700, mb: 1.5, color: "#6366f1" }}>
            Client
          </Typography>
          <InfoCard icon={<PersonIcon sx={{ fontSize: 18 }} />} label="Nom" value={customerName} />
          <InfoCard icon={<PhoneIcon sx={{ fontSize: 18 }} />} label="Téléphone" value={customerPhone} />
          <InfoCard icon={<EmailIcon sx={{ fontSize: 18 }} />} label="Email" value={customerEmail} />
        </Paper>

        {/* Demande */}
        <Paper elevation={0} sx={{ p: 2.5, borderRadius: 2, border: "1px solid #e0e0e0" }}>
          <Typography variant="subtitle2" sx={{ fontWeight: 700, mb: 1.5, color: "#6366f1" }}>
            Demande
          </Typography>
          <InfoCard icon={<FlightIcon sx={{ fontSize: 18 }} />} label="Prestation" value={extracted.circuit_code || ctx.circuit_code} />
          <InfoCard icon={<EventIcon sx={{ fontSize: 18 }} />} label="Date souhaitée" value={extracted.preferred_date || ctx.date} />
          <InfoCard icon={<AccessTimeIcon sx={{ fontSize: 18 }} />} label="Heure souhaitée" value={extracted.preferred_time} />
          <InfoCard icon={<GroupIcon sx={{ fontSize: 18 }} />} label="Nombre de personnes" value={extracted.quantity ? String(extracted.quantity) : null} />
        </Paper>
      </Box>

      {/* Résumé IA */}
      {record.summary && (
        <Alert severity="info" sx={{ mb: 3, borderRadius: 2 }} icon={<SmartToyIcon />}>
          <Typography variant="subtitle2" sx={{ fontWeight: 600 }}>Résumé de l'appel (IA)</Typography>
          <Typography variant="body2">{record.summary}</Typography>
        </Alert>
      )}

      {/* Créneaux proposés */}
      {proposedSlots.length > 0 && (
        <Paper elevation={0} sx={{ p: 2.5, mb: 3, borderRadius: 2, border: isActionable ? "2px solid #ed6c02" : "1px solid #e0e0e0" }}>
          <Typography variant="subtitle2" sx={{ fontWeight: 700, mb: 1.5, color: isActionable ? "#ed6c02" : "#333" }}>
            {isActionable ? "Créneau à confirmer" : "Créneau demandé"}
          </Typography>
          <Table size="small">
            <TableHead>
              <TableRow>
                <TableCell sx={{ fontWeight: 600 }}>#</TableCell>
                <TableCell sx={{ fontWeight: 600 }}>Début</TableCell>
                <TableCell sx={{ fontWeight: 600 }}>Fin</TableCell>
                <TableCell sx={{ fontWeight: 600 }}>Machine</TableCell>
                <TableCell sx={{ fontWeight: 600 }}>Prix</TableCell>
                {isActionable && <TableCell sx={{ fontWeight: 600 }}>Action</TableCell>}
              </TableRow>
            </TableHead>
            <TableBody>
              {proposedSlots.map((slot: any, i: number) => (
                <TableRow key={i} sx={{ backgroundColor: isActionable ? "#fff8e1" : "transparent" }}>
                  <TableCell>{i + 1}</TableCell>
                  <TableCell sx={{ fontWeight: 600 }}>{slot.debut}</TableCell>
                  <TableCell>{slot.fin}</TableCell>
                  <TableCell>{slot.aeronef || "—"}</TableCell>
                  <TableCell>{slot.prix ? `${slot.prix} €` : "—"}</TableCell>
                  {isActionable && (
                    <TableCell>
                      <Button
                        size="small"
                        variant="contained"
                        color="success"
                        startIcon={<CheckCircleIcon />}
                        onClick={() => handleValidate(i)}
                        sx={{ textTransform: "none", fontSize: "0.8rem", borderRadius: 2, boxShadow: "none" }}
                      >
                        Confirmer ce créneau
                      </Button>
                    </TableCell>
                  )}
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </Paper>
      )}

      {/* Réservation créée */}
      {record.reservation && (
        <Alert severity="success" sx={{ mb: 3, borderRadius: 2 }}>
          <Typography variant="body2" sx={{ fontWeight: 600 }}>
            Réservation #{typeof record.reservation === "object" ? record.reservation.id : record.reservation.toString().replace(/.*\//, "")} créée
          </Typography>
          <Button
            size="small"
            href={`#/reservations/${typeof record.reservation === "object" ? record.reservation.id : record.reservation.toString().replace(/.*\//, "")}/show`}
            sx={{ textTransform: "none", mt: 0.5 }}
          >
            Voir la réservation
          </Button>
        </Alert>
      )}

      {/* Historique messages */}
      {record.messages && record.messages.length > 0 && (
        <Paper elevation={0} sx={{ p: 2.5, mb: 3, borderRadius: 2, border: "1px solid #e0e0e0" }}>
          <Typography variant="subtitle2" sx={{ fontWeight: 700, mb: 1.5 }}>
            Historique des échanges
          </Typography>
          <Box sx={{ display: "flex", flexDirection: "column", gap: 1.5, maxHeight: 400, overflowY: "auto" }}>
            {record.messages.map((msg: any, i: number) => (
              <Box
                key={i}
                sx={{
                  display: "flex",
                  justifyContent: msg.role === "customer" ? "flex-start" : "flex-end",
                }}
              >
                <Paper
                  elevation={0}
                  sx={{
                    p: 1.5,
                    maxWidth: "75%",
                    borderRadius: 2,
                    background: msg.role === "customer" ? "#e3f2fd" : msg.role === "system" ? "#fff3e0" : "#e8f5e9",
                  }}
                >
                  <Typography variant="caption" sx={{ fontWeight: 600, display: "block", mb: 0.5 }}>
                    {msg.role === "customer" ? "Client" : msg.role === "system" ? "Système" : "Assistant"}
                    {msg.timestamp && (
                      <span style={{ fontWeight: 400, marginLeft: 8, color: "#888" }}>
                        {new Date(msg.timestamp).toLocaleString("fr-FR", { hour: "2-digit", minute: "2-digit", day: "2-digit", month: "2-digit" })}
                      </span>
                    )}
                  </Typography>
                  <Typography variant="body2" sx={{ whiteSpace: "pre-wrap" }}>
                    {msg.content}
                  </Typography>
                </Paper>
              </Box>
            ))}
          </Box>
        </Paper>
      )}

      {/* Actions principales */}
      {!isClosed && (
        <Box sx={{ display: "flex", gap: 2, mb: 3 }}>
          {isActionable && (
            <Button
              variant="contained"
              color="success"
              size="large"
              startIcon={<CheckCircleIcon />}
              onClick={() => handleValidate(0)}
              sx={{ textTransform: "none", borderRadius: 2, boxShadow: "none", px: 4 }}
            >
              Confirmer la réservation
            </Button>
          )}
          <Button
            variant="outlined"
            color="error"
            size="large"
            startIcon={<CancelIcon />}
            onClick={() => setCancelDialogOpen(true)}
            sx={{ textTransform: "none", borderRadius: 2 }}
          >
            Refuser
          </Button>
        </Box>
      )}

      {/* Réponse manuelle (email) */}
      {record.channel === "email" && customerEmail && !isClosed && (
        <>
          <Divider sx={{ my: 3 }} />
          <Paper elevation={0} sx={{ p: 2.5, borderRadius: 2, border: "1px solid #e0e0e0" }}>
            <Typography variant="subtitle2" sx={{ fontWeight: 700, mb: 1 }}>
              Répondre manuellement au client
            </Typography>
            <MuiTextField
              multiline
              rows={4}
              fullWidth
              value={replyText}
              onChange={(e) => setReplyText(e.target.value)}
              placeholder="Tapez votre réponse..."
              sx={{ mb: 1.5 }}
            />
            <Button
              variant="contained"
              disabled={!replyText.trim() || replySending}
              onClick={handleReply}
              sx={{ textTransform: "none", borderRadius: 2 }}
            >
              {replySending ? "Envoi..." : "Envoyer"}
            </Button>
          </Paper>
        </>
      )}

      {/* Dialog confirmation annulation */}
      <Dialog open={cancelDialogOpen} onClose={() => setCancelDialogOpen(false)}>
        <DialogTitle>Refuser cette demande ?</DialogTitle>
        <DialogContent>
          <DialogContentText>
            {customerName} sera notifié que sa demande a été refusée. Cette action est irréversible.
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setCancelDialogOpen(false)}>Annuler</Button>
          <Button color="error" variant="contained" onClick={handleCancel}>
            Confirmer le refus
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};

export const ConversationThreadShow = () => (
  <Show title="Détail de la demande" actions={<ProtectedShowActions />}>
    <SimpleShowLayout>
      <ThreadDetail />
    </SimpleShowLayout>
  </Show>
);
