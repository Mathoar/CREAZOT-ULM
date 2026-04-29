"use client";

import {
  List,
  Datagrid,
  TextField,
  BooleanField,
  EditButton,
  FunctionField,
} from "react-admin";
import { Chip, Box } from "@mui/material";

const RESOURCE_LABELS: Record<string, string> = {
  agenda: "Agenda",
  reservations: "Réservations",
  prestations: "Carnets de vols",
  vols: "Vols",
  passagers: "Passagers",
  commercial: "Commercial",
  pilotes: "Pilotes",
  aeronefs: "Aéronefs",
  formations: "Formations",
  manex: "MANEX",
  evenements_securite: "Évén. sécurité",
  statistiques: "Statistiques",
  configuration: "Administration",
};

export const RoleList = () => (
  <List
    title="Rôles & Permissions"
    sort={{ field: "code", order: "ASC" }}
    perPage={25}
    exporter={false}
  >
    <Datagrid bulkActionButtons={false}>
      <TextField source="code" label="Code" />
      <TextField source="label" label="Libellé" />
      <BooleanField source="isSystem" label="Système" />
      <FunctionField
        label="Permissions"
        render={(record: any) => {
          const perms = record?.permissions || [];
          const readCount = perms.filter((p: any) => p.canRead).length;
          const writeCount = perms.filter((p: any) => p.canWrite).length;
          return (
            <Box display="flex" gap={0.5}>
              <Chip label={`${readCount} lecture`} size="small" color="info" variant="outlined" />
              <Chip label={`${writeCount} écriture`} size="small" color="success" variant="outlined" />
            </Box>
          );
        }}
      />
      <EditButton />
    </Datagrid>
  </List>
);
