"use client";

import { CreateButton, EditButton, TopToolbar, useResourceContext } from "react-admin";
import { usePermissions } from "./PermissionProvider";

const RESOURCE_TO_PERMISSION: Record<string, string> = {
  prestations: "prestations",
  vols: "vols",
  landings: "vols",
  passagers: "passagers",
  aeronefs: "aeronefs",
  entretiens: "aeronefs",
  profil_pilotes: "pilotes",
  disponibilites: "pilotes",
  cadeaux: "commercial",
  payments: "commercial",
  expenses: "commercial",
  reservations: "reservations",
  conversation_threads: "reservations",
  lessons: "formations",
  programmes: "formations",
  trainings: "formations",
  manex_sections: "manex",
  manex_versions: "manex",
  security_events: "evenements_securite",
  circuits: "configuration",
  options: "configuration",
  airports: "configuration",
  cameras: "configuration",
  flight_rules: "configuration",
  origines: "configuration",
  message_templates: "configuration",
  briefings: "configuration",
  clients: "configuration",
  client_access_requests: "configuration",
};

function useCanWriteResource(explicitResource?: string): boolean {
  const { canWrite } = usePermissions();
  const raResource = useResourceContext();

  if (explicitResource) return canWrite(explicitResource);

  const permResource = RESOURCE_TO_PERMISSION[raResource ?? ""];
  if (!permResource) return true;
  return canWrite(permResource);
}

export const ProtectedCreateButton = ({ resource, ...props }: { resource?: string; [k: string]: any }) => {
  const allowed = useCanWriteResource(resource);
  if (!allowed) return null;
  return <CreateButton {...props} />;
};

export const ProtectedEditButton = ({ resource, ...props }: { resource?: string; [k: string]: any }) => {
  const allowed = useCanWriteResource(resource);
  if (!allowed) return null;
  return <EditButton {...props} />;
};

/**
 * Show view actions toolbar: renders EditButton only if user has write permission.
 */
export const ProtectedShowActions = ({ resource }: { resource?: string }) => {
  const allowed = useCanWriteResource(resource);
  return (
    <TopToolbar>
      {allowed && <EditButton />}
    </TopToolbar>
  );
};
