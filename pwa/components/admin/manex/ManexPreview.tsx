import { useState, useEffect, useRef, useCallback } from "react";
import { Box, CircularProgress, Alert, Button } from "@mui/material";
import RefreshIcon from "@mui/icons-material/Refresh";
import { useClient } from "../ClientProvider";
import { useSessionContext } from "../SessionContextProvider";

const API_DOMAIN = process.env.NEXT_PUBLIC_ENTRYPOINT || "";

export const ManexPreview = () => {
  const { client } = useClient();
  const { session } = useSessionContext();
  const [html, setHtml] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);
  const iframeRef = useRef<HTMLIFrameElement>(null);

  const resizeIframe = useCallback(() => {
    const iframe = iframeRef.current;
    if (!iframe?.contentDocument?.body) return;
    iframe.style.height = iframe.contentDocument.body.scrollHeight + 40 + "px";
  }, []);

  const load = async () => {
    setLoading(true);
    setError(false);
    try {
      const headers: Record<string, string> = {
        Authorization: `Bearer ${session?.accessToken}`,
      };
      if (client?.id) headers["X-Client-Id"] = String(client.id);

      const res = await fetch(`${API_DOMAIN}/admin/manex/preview`, { headers });
      if (!res.ok) throw new Error();
      const text = await res.text();
      setHtml(text);
    } catch {
      setError(true);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load();
  }, [client?.id]);

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" p={4}>
        <CircularProgress />
      </Box>
    );
  }

  if (error) {
    return <Alert severity="error">Erreur lors du chargement de l&apos;aperçu.</Alert>;
  }

  return (
    <Box>
      <Box display="flex" justifyContent="flex-end" mb={1}>
        <Button startIcon={<RefreshIcon />} onClick={load} size="small">
          Rafraîchir
        </Button>
      </Box>
      <Box
        sx={{
          border: "1px solid #e0e0e0",
          borderRadius: 1,
          backgroundColor: "#fff",
          overflow: "auto",
          p: 0,
        }}
      >
        <iframe
          ref={iframeRef}
          srcDoc={html ?? ""}
          onLoad={resizeIframe}
          style={{ width: "100%", minHeight: "75vh", border: "none" }}
          title="Aperçu MANEX"
        />
      </Box>
    </Box>
  );
};
