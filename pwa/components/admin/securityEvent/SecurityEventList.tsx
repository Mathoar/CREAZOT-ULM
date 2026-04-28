import {
  List,
  Datagrid,
  TextField,
  DateField,
  ReferenceField,
  FunctionField,
  useGetList,
} from "react-admin";
import { Chip, Alert, Box } from "@mui/material";

const typeChoices = [
  { id: "incident", name: "Incident" },
  { id: "accident", name: "Accident" },
  { id: "quasi_accident", name: "Quasi-accident" },
  { id: "observation", name: "Observation" },
  { id: "note_interne", name: "Note interne" },
];

const statusColors: Record<string, "warning" | "error" | "success" | "default"> = {
  ouvert: "error",
  en_cours: "warning",
  clos: "success",
};

const RegulatoryReminder = () => {
  const { data } = useGetList("site-settings", { pagination: { page: 1, perPage: 1 } });
  const settings = data?.[0];
  const delaiDGAC = settings?.delaiNotificationDGACHeures ?? 72;
  const delaiCR = settings?.delaiCompteRenduSuiviJours ?? 30;

  return (
    <Alert severity="info" sx={{ mb: 2 }}>
      <strong>Rappel réglementaire :</strong> notification à l'exploitant sous {delaiDGAC}h,
      compte-rendu de suivi sous {delaiCR} jours avec mesures correctives.
      En cas d'accident, notification au BEA obligatoire.
    </Alert>
  );
};

export const SecurityEventList = () => (
  <List sort={{ field: "createdAt", order: "DESC" }} perPage={25}>
    <Box>
      <RegulatoryReminder />
    </Box>
    <Datagrid rowClick="edit" bulkActionButtons={false}>
      <DateField source="dateEvenement" label="Date" />
      <FunctionField
        label="Type"
        render={(record: any) => {
          const choice = typeChoices.find((c) => c.id === record.type);
          return choice?.name ?? record.type;
        }}
      />
      <TextField source="description" label="Description" sortable={false} />
      <ReferenceField source="pilote" reference="users" label="Pilote" link={false}>
        <FunctionField render={(r: any) => `${r.firstName ?? ""} ${r.lastName ?? ""}`} />
      </ReferenceField>
      <ReferenceField source="aeronef" reference="aeronefs" label="Aéronef" link={false}>
        <TextField source="immatriculation" />
      </ReferenceField>
      <FunctionField
        label="Statut"
        render={(record: any) => (
          <Chip
            label={record.status === "en_cours" ? "En cours" : record.status === "clos" ? "Clos" : "Ouvert"}
            color={statusColors[record.status] ?? "default"}
            size="small"
          />
        )}
      />
      <DateField source="createdAt" label="Créé le" showTime />
    </Datagrid>
  </List>
);
