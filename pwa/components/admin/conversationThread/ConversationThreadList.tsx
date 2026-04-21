import { useState } from "react";
import { useAiReservationStats } from "../../../app/lib/mercure";
import {
  Datagrid,
  DateField,
  FunctionField,
  List,
  TextField,
  TextInput,
  SelectInput,
  TopToolbar,
  FilterButton,
  useNotify,
  useRefresh,
} from "react-admin";
import {
  Chip,
  Box,
  Button,
  Typography,
  Paper,
  Tooltip,
  IconButton,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
} from "@mui/material";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import CancelIcon from "@mui/icons-material/Cancel";
import PhoneIcon from "@mui/icons-material/Phone";
import SmartToyIcon from "@mui/icons-material/SmartToy";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import EventIcon from "@mui/icons-material/Event";
import FlightIcon from "@mui/icons-material/Flight";
import PersonIcon from "@mui/icons-material/Person";
import { useSessionContext } from "../SessionContextProvider";
import { useClient } from "../ClientProvider";
import axios from "axios";
import { ENTRYPOINT } from "../../../config/entrypoint";

const rawId = (record: any): string => {
  const id = record?.id ?? record?.['@id'] ?? '';
  return String(id).replace(/.*\//, '');
};

const statusConfig: Record<string, { label: string; color: "default" | "warning" | "info" | "primary" | "success" | "error" }> = {
  pending: { label: "En cours", color: "info" },
  analyzing: { label: "Analyse IA", color: "info" },
  proposing: { label: "Créneaux proposés", color: "primary" },
  awaiting_customer: { label: "Attente client", color: "info" },
  awaiting_club: { label: "À valider", color: "warning" },
  confirmed: { label: "Confirmée", color: "success" },
  cancelled: { label: "Annulée", color: "error" },
  expired: { label: "Expirée", color: "default" },
};

const channelLabels: Record<string, { icon: string; label: string }> = {
  email: { icon: "📧", label: "Email" },
  voice: { icon: "📞", label: "Vocal" },
  sms: { icon: "💬", label: "SMS" },
  whatsapp: { icon: "💬", label: "WhatsApp" },
};

const conversationFilters = [
  <TextInput key="customerName" source="customerName" label="Nom client" alwaysOn />,
  <SelectInput
    key="status"
    source="status"
    label="Statut"
    choices={Object.entries(statusConfig).map(([id, { label }]) => ({ id, name: label }))}
    alwaysOn
  />,
  <SelectInput
    key="channel"
    source="channel"
    label="Canal"
    choices={[
      { id: "email", name: "Email" },
      { id: "voice", name: "Vocal" },
    ]}
  />,
];

const StatsBar = ({ stats }: { stats: Record<string, number> }) => {
  const items = [
    { key: "awaiting_club", label: "À valider", color: "#ed6c02", bg: "#fff3e0" },
    { key: "pending", label: "En cours", color: "#0288d1", bg: "#e1f5fe" },
    { key: "confirmed", label: "Confirmées", color: "#2e7d32", bg: "#e8f5e9" },
    { key: "cancelled", label: "Annulées", color: "#d32f2f", bg: "#fbe9e7" },
  ];

  return (
    <Box sx={{ display: "flex", gap: 2, mb: 2, flexWrap: "wrap" }}>
      {items.map(({ key, label, color, bg }) => (
        <Paper
          key={key}
          elevation={0}
          sx={{
            px: 2.5,
            py: 1.5,
            borderRadius: 2,
            background: bg,
            border: `1px solid ${color}22`,
            minWidth: 120,
            textAlign: "center",
          }}
        >
          <Typography variant="h5" sx={{ fontWeight: 700, color }}>
            {stats[key] ?? 0}
          </Typography>
          <Typography variant="caption" sx={{ color, fontWeight: 500 }}>
            {label}
          </Typography>
        </Paper>
      ))}
      <Paper
        elevation={0}
        sx={{
          px: 2.5,
          py: 1.5,
          borderRadius: 2,
          background: "#f5f5f5",
          border: "1px solid #e0e0e0",
          minWidth: 120,
          textAlign: "center",
        }}
      >
        <Typography variant="h5" sx={{ fontWeight: 700, color: "#616161" }}>
          {Object.values(stats).reduce((a, b) => a + b, 0)}
        </Typography>
        <Typography variant="caption" sx={{ color: "#616161", fontWeight: 500 }}>
          Total
        </Typography>
      </Paper>
    </Box>
  );
};

const RequestSummary = ({ record }: { record: any }) => {
  const ctx = record?.aiContext || {};
  const extracted = ctx.extracted || {};
  const slots = ctx.proposed_slots || [];
  const slot = slots[0];

  if (!extracted.circuit_code && !extracted.preferred_date && !slot) {
    return (
      <Typography variant="body2" color="text.secondary" sx={{ fontStyle: "italic" }}>
        {record?.summary || "Appel en cours..."}
      </Typography>
    );
  }

  return (
    <Box sx={{ display: "flex", gap: 1.5, alignItems: "center", flexWrap: "wrap" }}>
      {extracted.circuit_code && (
        <Chip
          icon={<FlightIcon sx={{ fontSize: 14 }} />}
          label={extracted.circuit_code}
          size="small"
          variant="outlined"
          sx={{ fontSize: "0.75rem" }}
        />
      )}
      {(extracted.preferred_date || slot?.debut) && (
        <Chip
          icon={<EventIcon sx={{ fontSize: 14 }} />}
          label={extracted.preferred_date || slot?.debut?.split(" ")[0]}
          size="small"
          variant="outlined"
          sx={{ fontSize: "0.75rem" }}
        />
      )}
      {(extracted.preferred_time || slot?.debut) && (
        <Chip
          icon={<AccessTimeIcon sx={{ fontSize: 14 }} />}
          label={extracted.preferred_time || slot?.debut?.split(" ")[1]}
          size="small"
          variant="outlined"
          sx={{ fontSize: "0.75rem" }}
        />
      )}
      {extracted.quantity && extracted.quantity > 1 && (
        <Chip
          icon={<PersonIcon sx={{ fontSize: 14 }} />}
          label={`${extracted.quantity} pers.`}
          size="small"
          variant="outlined"
          sx={{ fontSize: "0.75rem" }}
        />
      )}
    </Box>
  );
};

const ListActions = () => (
  <TopToolbar>
    <FilterButton />
  </TopToolbar>
);

export const ConversationThreadList = () => {
  const { session } = useSessionContext();
  const { client } = useClient();
  const notify = useNotify();
  const refresh = useRefresh();
  const stats = useAiReservationStats(client?.id, session?.accessToken);
  const [cancelDialogRecord, setCancelDialogRecord] = useState<any>(null);

  const handleValidate = async (record: any) => {
    try {
      await axios.post(
        `${ENTRYPOINT}/admin/ai-reservation/conversations/${rawId(record)}/validate`,
        { slotIndex: 0 },
        { headers: { Authorization: `Bearer ${session?.accessToken}` } }
      );
      notify("Réservation confirmée — le client sera notifié", { type: "success" });
      refresh();
      // Stats are pushed back via Mercure right after the backend persist.
    } catch (err: any) {
      notify(err?.response?.data?.error || "Erreur lors de la validation", { type: "error" });
    }
  };

  const handleCancel = async (record: any) => {
    try {
      await axios.post(
        `${ENTRYPOINT}/admin/ai-reservation/conversations/${rawId(record)}/cancel`,
        {},
        { headers: { Authorization: `Bearer ${session?.accessToken}` } }
      );
      notify("Demande annulée — le client sera notifié", { type: "info" });
      refresh();
      setCancelDialogRecord(null);
    } catch (err: any) {
      notify(err?.response?.data?.error || "Erreur lors de l'annulation", { type: "error" });
    }
  };

  return (
    <Box>
      <Box sx={{ px: 2, pt: 2 }}>
        <Box sx={{ display: "flex", alignItems: "center", gap: 1.5, mb: 2 }}>
          <SmartToyIcon sx={{ fontSize: 28, color: "#6366f1" }} />
          <Typography variant="h6" sx={{ fontWeight: 700 }}>
            Assistant IA — Demandes de réservation
          </Typography>
        </Box>
        <StatsBar stats={stats} />
      </Box>

      <List
        filters={conversationFilters}
        actions={<ListActions />}
        sort={{ field: "createdAt", order: "DESC" }}
        perPage={25}
        title=" "
        sx={{
          "& .RaList-main": { mt: 0 },
          "& .RaDatagrid-row": {
            "&:hover": { backgroundColor: "#f8f9ff" },
          },
        }}
      >
        <Datagrid
          bulkActionButtons={false}
          rowClick="show"
          sx={{
            "& .RaDatagrid-headerCell": {
              fontWeight: 600,
              backgroundColor: "#fafafa",
            },
            "& .MuiTableRow-root": {
              borderLeft: "3px solid transparent",
            },
            "& .MuiTableRow-root:has(td .awaiting-club-marker)": {
              borderLeft: "3px solid #ed6c02",
              backgroundColor: "#fffbf5",
            },
          }}
        >
          <FunctionField
            label="Canal"
            render={(record: any) => {
              const ch = channelLabels[record?.channel] || { icon: "?", label: record?.channel };
              return (
                <Tooltip title={ch.label}>
                  <Typography variant="body1" sx={{ fontSize: "1.2rem" }}>
                    {ch.icon}
                  </Typography>
                </Tooltip>
              );
            }}
          />
          <FunctionField
            label="Client"
            render={(record: any) => {
              const name = record?.customerName || record?.aiContext?.extracted?.customer_name || "—";
              const phone = record?.customerPhone;
              return (
                <Box>
                  <Typography variant="body2" sx={{ fontWeight: 600 }}>
                    {name}
                  </Typography>
                  {phone && (
                    <Typography variant="caption" color="text.secondary" sx={{ display: "flex", alignItems: "center", gap: 0.3 }}>
                      <PhoneIcon sx={{ fontSize: 12 }} /> {phone}
                    </Typography>
                  )}
                </Box>
              );
            }}
          />
          <FunctionField
            label="Demande"
            render={(record: any) => <RequestSummary record={record} />}
          />
          <FunctionField
            label="Statut"
            render={(record: any) => {
              const cfg = statusConfig[record?.status] || { label: record?.status, color: "default" as const };
              return (
                <>
                  {record?.status === "awaiting_club" && <span className="awaiting-club-marker" style={{ display: "none" }} />}
                  <Chip label={cfg.label} color={cfg.color} size="small" sx={{ fontWeight: 600 }} />
                </>
              );
            }}
          />
          <DateField source="createdAt" label="Date" showTime locales="fr-FR" />
          <FunctionField
            label="Actions"
            render={(record: any) => (
              <Box sx={{ display: "flex", gap: 0.5 }} onClick={(e: any) => e.stopPropagation()}>
                {record?.status === "awaiting_club" && (
                  <>
                    <Button
                      size="small"
                      variant="contained"
                      color="success"
                      startIcon={<CheckCircleIcon />}
                      onClick={() => handleValidate(record)}
                      sx={{ textTransform: "none", fontSize: "0.75rem", borderRadius: 2, boxShadow: "none" }}
                    >
                      Confirmer
                    </Button>
                    <Button
                      size="small"
                      variant="outlined"
                      color="error"
                      startIcon={<CancelIcon />}
                      onClick={() => setCancelDialogRecord(record)}
                      sx={{ textTransform: "none", fontSize: "0.75rem", borderRadius: 2 }}
                    >
                      Refuser
                    </Button>
                  </>
                )}
                {record?.status !== "awaiting_club" && record?.status !== "confirmed" && record?.status !== "cancelled" && (
                  <Button
                    size="small"
                    variant="outlined"
                    color="error"
                    startIcon={<CancelIcon />}
                    onClick={() => setCancelDialogRecord(record)}
                    sx={{ textTransform: "none", fontSize: "0.75rem", borderRadius: 2 }}
                  >
                    Annuler
                  </Button>
                )}
              </Box>
            )}
          />
          <FunctionField
            label="Résa"
            render={(record: any) => {
              if (!record?.reservation) return null;
              const resaId = typeof record.reservation === "object"
                ? record.reservation.id
                : record.reservation.toString().replace(/.*\//, "");
              return (
                <Chip
                  label={`#${resaId}`}
                  size="small"
                  color="success"
                  variant="outlined"
                  onClick={(e: any) => {
                    e.stopPropagation();
                    window.location.hash = `#/reservations/${resaId}/show`;
                  }}
                  sx={{ cursor: "pointer", fontWeight: 600 }}
                />
              );
            }}
          />
        </Datagrid>
      </List>

      <Dialog open={!!cancelDialogRecord} onClose={() => setCancelDialogRecord(null)}>
        <DialogTitle>Refuser cette demande ?</DialogTitle>
        <DialogContent>
          <DialogContentText>
            Le client {cancelDialogRecord?.customerName || ""} sera notifié de l'annulation.
            Cette action est irréversible.
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setCancelDialogRecord(null)}>Annuler</Button>
          <Button color="error" variant="contained" onClick={() => handleCancel(cancelDialogRecord)}>
            Confirmer le refus
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};
