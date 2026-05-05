import { useEffect, useState } from "react";
import { Box, Chip, Typography } from "@mui/material";
import { useWatch } from "react-hook-form";
import { useSessionContext } from "../SessionContextProvider";
import { useClient } from "../ClientProvider";

const API_DOMAIN = process.env.NEXT_PUBLIC_ENTRYPOINT || "";

const TYPICAL_VALUES: Record<string, string> = {
  nom: "Jean Dupont",
  circuit: "Découverte 30min",
  date: "23/04/2026",
  heure: "08:30",
  pilote: "Marc Hoarau",
  code: "RESA-1773645712810-UN206N",
  structure: "Planetair974",
  enseigne: "Planetair974",
  telephone: "+33612345678",
  email: "passager@exemple.fr",
  nb_personnes: "2",
  lien: "https://logic-ciel.com/r/aB3xK9pQ",
};

const resolveTypical = (body: string): string =>
  body.replace(/\{\{(\w+)\}\}/g, (m, key) => TYPICAL_VALUES[key] ?? m);

export interface SmsPreview {
  sanitized: string;
  encoding: string;
  length: number;
  units: number;
  segments: number;
  replacedChars: number;
  unsupportedChars: string[];
  costPerUnit: number | null;
  estimatedCostPerSms: number | null;
}

interface SmsLiveCounterProps {
  body: string;
  resolveVariables?: boolean;
  multiplier?: number;
  multiplierLabel?: string;
  helperText?: string;
}

export const SmsLiveCounter = ({
  body,
  resolveVariables = false,
  multiplier = 0,
  multiplierLabel = "envoi",
  helperText,
}: SmsLiveCounterProps) => {
  const { session } = useSessionContext();
  const { client } = useClient();
  const [preview, setPreview] = useState<SmsPreview | null>(null);
  const hasSms = client?.hasSMS === true;

  useEffect(() => {
    if (!hasSms) {
      setPreview(null);
      return;
    }
    if (!body || !body.trim()) {
      setPreview(null);
      return;
    }
    const handle = setTimeout(async () => {
      try {
        const payload = resolveVariables ? resolveTypical(body) : body;
        const res = await fetch(`${API_DOMAIN}/admin/notifications/sms-preview`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${session?.accessToken}`,
            "X-Client-Id": String(client?.id ?? ""),
          },
          body: JSON.stringify({ body: payload }),
        });
        if (res.ok) {
          setPreview(await res.json());
        }
      } catch (e) {
        console.error("Erreur preview SMS", e);
      }
    }, 300);
    return () => clearTimeout(handle);
  }, [body, resolveVariables, session?.accessToken, client?.id, hasSms]);

  if (!hasSms || !preview) return null;

  const segColor: "success" | "warning" | "error" =
    preview.segments <= 1 ? "success" :
    preview.segments <= 3 ? "warning" : "error";

  const totalCost =
    multiplier > 0 && preview.estimatedCostPerSms !== null
      ? preview.estimatedCostPerSms * multiplier
      : null;

  return (
    <Box
      sx={{
        mt: 1,
        mb: 1,
        p: 1.5,
        bgcolor: "grey.50",
        border: 1,
        borderColor: "grey.300",
        borderRadius: 1,
      }}
    >
      <Box display="flex" gap={1} flexWrap="wrap" alignItems="center">
        <Chip size="small" label={`${preview.length} car. (${preview.units} unités)`} />
        <Chip
          size="small"
          color={preview.encoding === "GSM-7" ? "success" : "warning"}
          label={`Encodage : ${preview.encoding}`}
        />
        <Chip
          size="small"
          color={segColor}
          label={`${preview.segments} segment${preview.segments > 1 ? "s" : ""}`}
        />
        {preview.estimatedCostPerSms !== null && (
          <>
            <Chip
              size="small"
              variant="outlined"
              label={`Coût/SMS : ${preview.estimatedCostPerSms.toFixed(4)} €`}
            />
            {totalCost !== null && multiplier > 0 && (
              <Chip
                size="small"
                variant="outlined"
                color="primary"
                label={`Total ${multiplier} ${multiplierLabel}${multiplier > 1 ? "s" : ""} : ${totalCost.toFixed(4)} €`}
              />
            )}
          </>
        )}
      </Box>
      {preview.replacedChars > 0 && (
        <Typography variant="caption" color="text.secondary" display="block" sx={{ mt: 0.5 }}>
          {preview.replacedChars} caractère{preview.replacedChars > 1 ? "s" : ""} non-GSM-7 remplacé{preview.replacedChars > 1 ? "s" : ""} automatiquement à l'envoi (apostrophes courbes, tirets longs, accents non supportés…).
        </Typography>
      )}
      {preview.unsupportedChars.length > 0 && (
        <Typography variant="caption" color="warning.main" display="block">
          Caractères non encodables en GSM-7 (forcent UCS-2) : {preview.unsupportedChars.join(" ")}
        </Typography>
      )}
      {preview.encoding === "UCS-2" && (
        <Typography variant="caption" color="warning.main" display="block">
          Limite par segment : 70 caractères en UCS-2 (vs 160 en GSM-7).
        </Typography>
      )}
      {helperText && (
        <Typography variant="caption" color="text.secondary" display="block" sx={{ mt: 0.5, fontStyle: "italic" }}>
          {helperText}
        </Typography>
      )}
    </Box>
  );
};

interface SmsLiveCounterFieldProps {
  source: string;
  resolveVariables?: boolean;
  multiplier?: number;
  multiplierLabel?: string;
  helperText?: string;
}

export const SmsLiveCounterField = ({ source, ...rest }: SmsLiveCounterFieldProps) => {
  const value = useWatch({ name: source }) as string | undefined;
  return <SmsLiveCounter body={value ?? ""} {...rest} />;
};
