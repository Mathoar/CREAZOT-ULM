import { useState, useCallback } from "react";
import {
  Box, Typography, Accordion, AccordionSummary, AccordionDetails,
  Chip, Switch, FormControlLabel,
} from "@mui/material";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import SaveIcon from "@mui/icons-material/Save";
import AutoFixHighIcon from "@mui/icons-material/AutoFixHigh";
import EditNoteIcon from "@mui/icons-material/EditNote";
import { useNotify, TextInput } from "react-admin";
import { RichTextInput } from "ra-input-rich-text";
import { Form, SaveButton, Toolbar } from "react-admin";
import { useSessionContext } from "../SessionContextProvider";
import { useClient } from "../ClientProvider";
import { RichTextWithTables } from "./RichTextWithTables";

const API_DOMAIN = process.env.NEXT_PUBLIC_ENTRYPOINT || "";

interface ManexSectionRecord {
  "@id": string;
  id: number;
  sectionKey: string;
  title: string;
  position: number;
  isEnabled: boolean;
  introHtml: string | null;
  customHtml: string | null;
  hasAutoContent: boolean;
  updatedAt: string | null;
}

interface Props {
  sections: ManexSectionRecord[];
  onSectionsChange: (sections: ManexSectionRecord[]) => void;
}

const SectionToolbar = () => (
  <Toolbar sx={{ display: "flex", justifyContent: "flex-end", backgroundColor: "transparent" }}>
    <SaveButton label="Sauvegarder" icon={<SaveIcon />} />
  </Toolbar>
);

const SectionRow = ({
  section,
  onSectionUpdate,
}: {
  section: ManexSectionRecord;
  onSectionUpdate: (updated: ManexSectionRecord) => void;
}) => {
  const { session } = useSessionContext();
  const { client } = useClient();
  const notify = useNotify();

  const getHeaders = useCallback((): Record<string, string> => {
    const h: Record<string, string> = {
      "Content-Type": "application/merge-patch+json",
      Authorization: `Bearer ${session?.accessToken}`,
    };
    if (client?.id) h["X-Client-Id"] = String(client.id);
    return h;
  }, [session?.accessToken, client?.id]);

  const patchSection = useCallback(async (body: Record<string, unknown>): Promise<ManexSectionRecord | null> => {
    const res = await fetch(`${API_DOMAIN}${section["@id"]}`, {
      method: "PATCH",
      headers: getHeaders(),
      body: JSON.stringify(body),
    });
    if (!res.ok) throw new Error(`PATCH failed (${res.status})`);
    return await res.json();
  }, [section["@id"], getHeaders]);

  const handleToggleEnabled = async () => {
    const newVal = !section.isEnabled;
    onSectionUpdate({ ...section, isEnabled: newVal });

    try {
      await patchSection({ isEnabled: newVal });
    } catch {
      onSectionUpdate({ ...section, isEnabled: !newVal });
      notify("Erreur lors de la mise à jour", { type: "error" });
    }
  };

  const handleSubmit = async (values: any) => {
    try {
      const updated = await patchSection({
        title: values.title,
        introHtml: values.introHtml || null,
        customHtml: values.customHtml || null,
      });
      if (updated) onSectionUpdate({ ...section, ...updated });
      notify("Section sauvegardée", { type: "success" });
    } catch {
      notify("Erreur lors de la sauvegarde", { type: "error" });
    }
  };

  return (
    <Accordion defaultExpanded={false} TransitionProps={{ unmountOnExit: true }}>
      <AccordionSummary expandIcon={<ExpandMoreIcon />}>
        <Box display="flex" alignItems="center" gap={2} width="100%">
          <Typography sx={{ fontWeight: "bold", minWidth: 30 }}>
            {section.position}.
          </Typography>
          <Typography sx={{ flexGrow: 1 }}>{section.title}</Typography>
          {section.hasAutoContent && (
            <Chip icon={<AutoFixHighIcon />} label="Auto" size="small" color="info" variant="outlined" />
          )}
          <Chip
            label={section.isEnabled ? "Activée" : "Désactivée"}
            size="small"
            color={section.isEnabled ? "success" : "default"}
            variant="outlined"
          />
        </Box>
      </AccordionSummary>
      <AccordionDetails>
        <Box mb={2}>
          <FormControlLabel
            control={
              <Switch
                checked={section.isEnabled}
                onChange={handleToggleEnabled}
                color="success"
              />
            }
            label={section.isEnabled ? "Section activée" : "Section désactivée"}
          />
        </Box>
        <Form record={section} onSubmit={handleSubmit}>
          <Box display="flex" flexDirection="column" gap={2}>
            <TextInput source="title" label="Titre de la section" fullWidth />

            <Box>
              <Typography variant="subtitle2" gutterBottom sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                <EditNoteIcon fontSize="small" /> Introduction (optionnel)
              </Typography>
              <RichTextInput source="introHtml" label={false} fullWidth />
            </Box>

            <Box>
              <Typography variant="subtitle2" gutterBottom sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                <EditNoteIcon fontSize="small" /> Contenu personnalisé (optionnel)
              </Typography>
              <RichTextWithTables source="customHtml" />
            </Box>

            <SectionToolbar />
          </Box>
        </Form>
      </AccordionDetails>
    </Accordion>
  );
};

export const ManexSectionEditor = ({ sections, onSectionsChange }: Props) => {
  const handleSectionUpdate = (updated: ManexSectionRecord) => {
    onSectionsChange(
      sections.map((s) => (s.id === updated.id ? updated : s))
    );
  };

  return (
    <Box>
      <Typography variant="subtitle1" gutterBottom color="text.secondary">
        Configurez chaque section de votre MANEX. Les sections marquées «&nbsp;Auto&nbsp;» sont alimentées
        automatiquement par les données de votre exploitation. Vous pouvez enrichir chaque section
        avec du contenu personnalisé.
      </Typography>
      {[...sections].sort((a, b) => a.position - b.position).map((section) => (
        <SectionRow
          key={section.id}
          section={section}
          onSectionUpdate={handleSectionUpdate}
        />
      ))}
    </Box>
  );
};
