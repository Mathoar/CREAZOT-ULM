import { useState, useEffect, useCallback } from "react";
import {
  Box, Typography, Paper, Tabs, Tab, Button, CircularProgress,
  Dialog, DialogTitle, DialogContent, DialogActions, TextField,
  Alert,
} from "@mui/material";
import PictureAsPdfIcon from "@mui/icons-material/PictureAsPdf";
import HistoryIcon from "@mui/icons-material/History";
import VisibilityIcon from "@mui/icons-material/Visibility";
import EditIcon from "@mui/icons-material/Edit";
import { Title, useDataProvider, useNotify } from "react-admin";
import { useClient } from "../ClientProvider";
import { useSessionContext } from "../SessionContextProvider";
import { usePermissions } from "../PermissionProvider";
import { ManexSectionEditor } from "./ManexSectionEditor";
import { ManexPreview } from "./ManexPreview";
import { ManexHistory } from "./ManexHistory";

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

export const ManexPage = () => {
  const { client } = useClient();
  const { session } = useSessionContext();
  const dataProvider = useDataProvider();
  const notify = useNotify();
  const { canWrite } = usePermissions();
  const hasWriteAccess = canWrite("manex");

  const [tab, setTab] = useState(hasWriteAccess ? 0 : 1);
  const [sections, setSections] = useState<ManexSectionRecord[]>([]);
  const [initialLoading, setInitialLoading] = useState(true);
  const [generating, setGenerating] = useState(false);
  const [generateDialogOpen, setGenerateDialogOpen] = useState(false);
  const [changelog, setChangelog] = useState("");
  const [refreshKey, setRefreshKey] = useState(0);

  const headers: Record<string, string> = {
    Authorization: `Bearer ${session?.accessToken}`,
  };
  if (client?.id) headers["X-Client-Id"] = String(client.id);

  const loadSections = useCallback(async () => {
    if (!client?.id) return;
    if (!hasWriteAccess) {
      setInitialLoading(false);
      return;
    }
    try {
      await fetch(`${API_DOMAIN}/admin/manex/ensure-sections`, {
        method: "POST",
        headers,
      });

      const { data } = await dataProvider.getList("manex_sections", {
        pagination: { page: 1, perPage: 100 },
        sort: { field: "position", order: "ASC" },
        filter: {},
      });
      setSections(data as ManexSectionRecord[]);
    } catch (e) {
      notify("Erreur lors du chargement des sections", { type: "error" });
    } finally {
      setInitialLoading(false);
    }
  }, [client?.id, dataProvider, notify, hasWriteAccess]);

  useEffect(() => {
    loadSections();
  }, [loadSections]);

  const handleGenerate = async () => {
    setGenerating(true);
    try {
      const res = await fetch(`${API_DOMAIN}/admin/manex/generate`, {
        method: "POST",
        headers: { ...headers, "Content-Type": "application/json" },
        body: JSON.stringify({ changelog: changelog || null }),
      });
      if (!res.ok) throw new Error("Erreur génération");
      const result = await res.json();
      notify(`MANEX v${result.versionNumber} généré avec succès`, { type: "success" });
      setGenerateDialogOpen(false);
      setChangelog("");
      setRefreshKey((k) => k + 1);
      setTab(2);
    } catch (e) {
      notify("Erreur lors de la génération du MANEX", { type: "error" });
    } finally {
      setGenerating(false);
    }
  };

  if (initialLoading && sections.length === 0) {
    return (
      <Box display="flex" justifyContent="center" p={6}>
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box p={2}>
      <Title title="MANEX" />
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={2}>
        <Typography variant="h5" fontWeight="bold" color="primary">
          Manuel d&apos;Exploitation (MANEX)
        </Typography>
        {hasWriteAccess && (
          <Button
            variant="contained"
            color="primary"
            startIcon={<PictureAsPdfIcon />}
            onClick={() => setGenerateDialogOpen(true)}
            size="large"
          >
            Générer le MANEX
          </Button>
        )}
      </Box>

      <Paper>
        {hasWriteAccess ? (
          <Tabs value={tab} onChange={(_, v) => setTab(v)}>
            <Tab icon={<EditIcon />} label="Sections" iconPosition="start" />
            <Tab icon={<VisibilityIcon />} label="Aperçu" iconPosition="start" />
            <Tab icon={<HistoryIcon />} label="Historique" iconPosition="start" />
          </Tabs>
        ) : (
          <Tabs value={1}>
            <Tab icon={<VisibilityIcon />} label="Aperçu" iconPosition="start" value={1} />
          </Tabs>
        )}

        <Box p={2}>
          {tab === 0 && hasWriteAccess && (
            <ManexSectionEditor
              sections={sections}
              onSectionsChange={setSections}
            />
          )}
          {tab === 1 && (
            <ManexPreview />
          )}
          {tab === 2 && hasWriteAccess && (
            <ManexHistory key={refreshKey} />
          )}
        </Box>
      </Paper>

      <Dialog open={generateDialogOpen} onClose={() => setGenerateDialogOpen(false)} maxWidth="sm" fullWidth>
        <DialogTitle>Générer le MANEX</DialogTitle>
        <DialogContent>
          <Alert severity="info" sx={{ mb: 2 }}>
            Le MANEX sera généré à partir des données actuelles de votre exploitation.
            Toutes les modifications de sections seront prises en compte.
          </Alert>
          <TextField
            label="Notes de version (optionnel)"
            multiline
            rows={3}
            fullWidth
            value={changelog}
            onChange={(e) => setChangelog(e.target.value)}
            placeholder="Ex: Mise à jour des limites météo, ajout d'un nouveau circuit..."
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setGenerateDialogOpen(false)}>Annuler</Button>
          <Button
            variant="contained"
            onClick={handleGenerate}
            disabled={generating}
            startIcon={generating ? <CircularProgress size={18} /> : <PictureAsPdfIcon />}
          >
            {generating ? "Génération..." : "Générer"}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};
