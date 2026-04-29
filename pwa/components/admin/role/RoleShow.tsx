"use client";

import { Show, useRecordContext } from "react-admin";
import {
  Box,
  Typography,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  Chip,
  Divider,
} from "@mui/material";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import CancelIcon from "@mui/icons-material/Cancel";

const RESOURCE_META: Record<string, { label: string; hint?: string }> = {
  agenda: { label: "Agenda", hint: "Calendrier du tableau de bord" },
  reservations: { label: "Réservations", hint: "Planification (écriture), Assistant IA" },
  prestations: { label: "Carnets de vols" },
  vols: { label: "Vols", hint: "Atterrissages" },
  passagers: { label: "Passagers" },
  commercial: { label: "Commercial", hint: "Prépaiements, Dépenses, Paiements" },
  pilotes: { label: "Pilotes", hint: "Disponibilités" },
  aeronefs: { label: "Aéronefs", hint: "Maintenance" },
  formations: { label: "Formations", hint: "Leçons, Programmes" },
  manex: { label: "MANEX" },
  evenements_securite: { label: "Événements sécurité" },
  statistiques: { label: "Statistiques" },
  configuration: { label: "Administration", hint: "Circuits, Options, Règles de vol, Membres…" },
};

const BoolIcon = ({ value }: { value: boolean }) =>
  value
    ? <CheckCircleIcon fontSize="small" sx={{ color: "success.main" }} />
    : <CancelIcon fontSize="small" sx={{ color: "text.disabled" }} />;

const RoleShowContent = () => {
  const record = useRecordContext();
  if (!record) return null;

  const perms = record.permissions || [];

  return (
    <Box sx={{ p: 3 }}>
      <Box sx={{ display: "flex", gap: 3, alignItems: "center", mb: 2 }}>
        <Box>
          <Typography variant="caption" color="text.secondary">Code</Typography>
          <Typography variant="h6">{record.code}</Typography>
        </Box>
        <Box>
          <Typography variant="caption" color="text.secondary">Libellé</Typography>
          <Typography variant="h6">{record.label}</Typography>
        </Box>
        {record.isSystem && (
          <Chip label="Rôle système" size="small" color="info" />
        )}
      </Box>

      <Divider sx={{ mb: 3 }} />

      <Typography variant="subtitle1" fontWeight="bold" gutterBottom>
        Matrice des permissions
      </Typography>

      <TableContainer component={Paper} variant="outlined">
        <Table size="small">
          <TableHead>
            <TableRow sx={{ backgroundColor: "#f5f5f5" }}>
              <TableCell sx={{ fontWeight: "bold" }}>Ressource</TableCell>
              <TableCell align="center" sx={{ fontWeight: "bold", width: 100 }}>Lecture</TableCell>
              <TableCell align="center" sx={{ fontWeight: "bold", width: 100 }}>Écriture</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {perms.map((perm: any) => {
              const meta = RESOURCE_META[perm.resource];
              return (
              <TableRow key={perm.resource} hover>
                <TableCell>
                  {meta?.label || perm.resource}
                  {meta?.hint && (
                    <Typography variant="caption" display="block" sx={{ color: "text.secondary", fontStyle: "italic", lineHeight: 1.2, mt: 0.2 }}>
                      {meta.hint}
                    </Typography>
                  )}
                </TableCell>
                <TableCell align="center"><BoolIcon value={perm.canRead} /></TableCell>
                <TableCell align="center"><BoolIcon value={perm.canWrite} /></TableCell>
              </TableRow>
              );
            })}
          </TableBody>
        </Table>
      </TableContainer>
    </Box>
  );
};

export const RoleShow = () => (
  <Show title="Détail du rôle">
    <RoleShowContent />
  </Show>
);
