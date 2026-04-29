"use client";

import {
  Edit,
  SimpleForm,
  TextInput,
  useRecordContext,
  useNotify,
  useRedirect,
  SaveButton,
  Toolbar,
} from "react-admin";
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
  Checkbox,
  Divider,
} from "@mui/material";
import { useState, useEffect, useCallback, useRef } from "react";
import { useSession } from "next-auth/react";
import type { Session } from "../../../app/auth";

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

interface PermData {
  id?: number;
  resource: string;
  canRead: boolean;
  canWrite: boolean;
}

const PermissionMatrix = ({
  permsRef,
  onDirty,
}: {
  permsRef: React.MutableRefObject<PermData[]>;
  onDirty: () => void;
}) => {
  const record = useRecordContext();
  const [perms, setPerms] = useState<PermData[]>([]);

  useEffect(() => {
    if (record?.permissions) {
      const mapped = record.permissions.map((p: any) => ({
        id: p.id,
        resource: p.resource,
        canRead: p.canRead ?? false,
        canWrite: p.canWrite ?? false,
      }));
      setPerms(mapped);
      permsRef.current = mapped;
    }
  }, [record?.permissions, permsRef]);

  if (!record) return null;

  const handleToggle = (resource: string, field: "canRead" | "canWrite") => {
    setPerms(prev => {
      const updated = prev.map(p => {
        if (p.resource !== resource) return p;
        const copy = { ...p };
        copy[field] = !copy[field];
        if (field === "canWrite" && copy.canWrite) {
          copy.canRead = true;
        }
        if (field === "canRead" && !copy.canRead) {
          copy.canWrite = false;
        }
        return copy;
      });
      permsRef.current = updated;
      return updated;
    });
    onDirty();
  };

  return (
    <Box mt={2} sx={{ width: "100%" }}>
      <Typography variant="h6" gutterBottom>
        Matrice des permissions
      </Typography>
      <TableContainer component={Paper} variant="outlined" sx={{ width: "100%" }}>
        <Table size="small">
          <TableHead>
            <TableRow sx={{ backgroundColor: "#f5f5f5" }}>
              <TableCell sx={{ fontWeight: "bold" }}>Ressource</TableCell>
              <TableCell align="center" sx={{ fontWeight: "bold", width: 100 }}>Lecture</TableCell>
              <TableCell align="center" sx={{ fontWeight: "bold", width: 100 }}>Écriture</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {perms.map((perm) => {
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
                <TableCell align="center">
                  <Checkbox
                    checked={perm.canRead}
                    onChange={() => handleToggle(perm.resource, "canRead")}
                    color="primary"
                    size="small"
                  />
                </TableCell>
                <TableCell align="center">
                  <Checkbox
                    checked={perm.canWrite}
                    onChange={() => handleToggle(perm.resource, "canWrite")}
                    color="success"
                    size="small"
                  />
                </TableCell>
              </TableRow>
              );
            })}
          </TableBody>
        </Table>
      </TableContainer>
    </Box>
  );
};

const RoleEditForm = () => {
  const record = useRecordContext();
  const { data: session } = useSession() as { data: Session | null };
  const notify = useNotify();
  const redirect = useRedirect();
  const permsRef = useRef<PermData[]>([]);
  const [dirty, setDirty] = useState(false);

  const handleSave = useCallback(async (values: any) => {
    if (!record?.id || !session?.accessToken) return;

    const roleId = typeof record.id === "string" && record.id.includes("/")
      ? record.id.split("/").pop()
      : record.id;

    try {
      const res = await fetch(`/admin/roles/${roleId}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${session.accessToken}`,
        },
        body: JSON.stringify({
          label: values.label,
          permissions: permsRef.current.map(p => ({
            resource: p.resource,
            canRead: p.canRead,
            canWrite: p.canWrite,
          })),
        }),
      });

      if (res.ok) {
        notify("Rôle mis à jour", { type: "success" });
        redirect("list", "roles");
      } else {
        const err = await res.json().catch(() => null);
        notify(err?.error || "Erreur lors de la sauvegarde", { type: "error" });
      }
    } catch {
      notify("Erreur réseau", { type: "error" });
    }
  }, [record?.id, session?.accessToken, notify, redirect]);

  const toolbar = (
    <Toolbar>
      <SaveButton alwaysEnable={dirty} />
    </Toolbar>
  );

  return (
    <SimpleForm toolbar={toolbar} onSubmit={handleSave} sx={{ maxWidth: "100%" }}>
      <Box sx={{ display: "flex", gap: 2, width: "100%" }}>
        <TextInput source="code" label="Code" disabled sx={{ flex: 1 }} />
        <TextInput
          source="label"
          label="Libellé"
          sx={{ flex: 2 }}
          onChange={() => setDirty(true)}
        />
      </Box>
      <Divider sx={{ my: 2, width: "100%" }} />
      <PermissionMatrix permsRef={permsRef} onDirty={() => setDirty(true)} />
    </SimpleForm>
  );
};

export const RoleEdit = () => (
  <Edit title="Modifier le rôle" mutationMode="pessimistic">
    <RoleEditForm />
  </Edit>
);
