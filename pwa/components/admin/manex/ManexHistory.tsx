import { useState, useEffect } from "react";
import {
  Box, Typography, Table, TableHead, TableRow, TableCell,
  TableBody, CircularProgress, Alert, IconButton, Chip, Tooltip,
} from "@mui/material";
import DownloadIcon from "@mui/icons-material/Download";
import { useDataProvider } from "react-admin";
import { useClient } from "../ClientProvider";

interface ManexVersionRecord {
  "@id": string;
  id: number;
  versionNumber: string;
  generatedAt: string;
  generatedBy?: { firstName: string; lastName: string } | string;
  document?: { contentUrl: string } | string;
  changelog: string | null;
}

export const ManexHistory = () => {
  const { client } = useClient();
  const dataProvider = useDataProvider();
  const [versions, setVersions] = useState<ManexVersionRecord[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!client?.id) return;
    setLoading(true);
    dataProvider
      .getList("manex_versions", {
        pagination: { page: 1, perPage: 50 },
        sort: { field: "generatedAt", order: "DESC" },
        filter: {},
      })
      .then(({ data }) => setVersions(data as ManexVersionRecord[]))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [client?.id, dataProvider]);

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" p={4}>
        <CircularProgress />
      </Box>
    );
  }

  if (versions.length === 0) {
    return (
      <Alert severity="info">
        Aucune version générée. Cliquez sur « Générer le MANEX » pour créer la première version.
      </Alert>
    );
  }

  return (
    <Box>
      <Typography variant="subtitle1" gutterBottom color="text.secondary">
        Historique des versions générées
      </Typography>
      <Table size="small">
        <TableHead>
          <TableRow>
            <TableCell>Version</TableCell>
            <TableCell>Date</TableCell>
            <TableCell>Auteur</TableCell>
            <TableCell>Notes</TableCell>
            <TableCell align="right">PDF</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {versions.map((v) => {
            const author = typeof v.generatedBy === "object" && v.generatedBy
              ? `${v.generatedBy.firstName} ${v.generatedBy.lastName}`
              : "—";
            const docUrl = typeof v.document === "object" && v.document
              ? v.document.contentUrl
              : null;

            return (
              <TableRow key={v.id}>
                <TableCell>
                  <Chip label={`v${v.versionNumber}`} color="primary" size="small" />
                </TableCell>
                <TableCell>
                  {new Date(v.generatedAt).toLocaleDateString("fr-FR", {
                    day: "2-digit", month: "2-digit", year: "numeric",
                    hour: "2-digit", minute: "2-digit",
                  })}
                </TableCell>
                <TableCell>{author}</TableCell>
                <TableCell sx={{ maxWidth: 300 }}>
                  <Typography variant="body2" noWrap>{v.changelog ?? "—"}</Typography>
                </TableCell>
                <TableCell align="right">
                  {docUrl ? (
                    <Tooltip title="Télécharger le PDF">
                      <IconButton
                        href={docUrl}
                        target="_blank"
                        color="primary"
                        size="small"
                      >
                        <DownloadIcon />
                      </IconButton>
                    </Tooltip>
                  ) : (
                    "—"
                  )}
                </TableCell>
              </TableRow>
            );
          })}
        </TableBody>
      </Table>
    </Box>
  );
};
