import { useState, useEffect, useCallback } from "react";
import {
  TabbedForm,
  TextInput,
  BooleanInput,
  SelectInput,
  ArrayInput,
  SimpleFormIterator,
  ReferenceArrayInput,
  SelectArrayInput,
  FormDataConsumer,
  required,
} from "react-admin";
import { CircularProgress, Typography, Box, Alert } from "@mui/material";
import { useSessionContext } from "../SessionContextProvider";

const ENTRYPOINT = process.env.NEXT_PUBLIC_ENTRYPOINT || "";

const methodChoices = [
  { id: "GET", name: "GET" },
  { id: "POST", name: "POST" },
  { id: "PUT", name: "PUT" },
  { id: "PATCH", name: "PATCH" },
  { id: "DELETE", name: "DELETE" },
];

const transformerChoices = [
  { id: "float", name: "Float" },
  { id: "int", name: "Integer" },
  { id: "string", name: "String" },
  { id: "boolean", name: "Boolean" },
  { id: "datetime", name: "DateTime" },
  { id: "split_first", name: "Split virgule → 1er (float)" },
  { id: "split_second", name: "Split virgule → 2ème (float)" },
  { id: "json_decode", name: "JSON decode" },
];

interface EntityField {
  id: string;
  name: string;
}

interface EntityMeta {
  id: string;
  label: string;
  fields: EntityField[];
}


interface IntegrationPatternFormProps {
  defaultValues?: Record<string, any>;
}

export const IntegrationPatternForm = ({ defaultValues }: IntegrationPatternFormProps = {}) => {
  const [entities, setEntities] = useState<EntityMeta[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [moduleChoices, setModuleChoices] = useState<{ id: string; name: string }[]>([]);
  const [modulesLoading, setModulesLoading] = useState(true);
  const { session } = useSessionContext();

  const fetchEntities = useCallback(async () => {
    try {
      const res = await fetch(`${ENTRYPOINT}/admin/integrations/entities`, {
        headers: { Authorization: `Bearer ${session?.accessToken}` },
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();
      setEntities(data);
    } catch (e: any) {
      setError(e.message);
      setEntities([
        { id: "client", label: "Client", fields: [] },
        { id: "aeronef", label: "Aeronef", fields: [] },
        { id: "siteSettings", label: "SiteSettings", fields: [] },
      ]);
    } finally {
      setLoading(false);
    }
  }, [session?.accessToken]);

  const fetchModules = useCallback(async () => {
    try {
      const res = await fetch(`${ENTRYPOINT}/admin/integrations/modules`, {
        headers: { Authorization: `Bearer ${session?.accessToken}` },
      });
      if (res.ok) setModuleChoices(await res.json());
    } catch { /* fallback silencieux */ }
    setModulesLoading(false);
  }, [session?.accessToken]);

  useEffect(() => {
    if (session?.accessToken) { fetchEntities(); fetchModules(); }
  }, [session?.accessToken, fetchEntities, fetchModules]);

  const sourceChoices = [
    ...entities.map((e) => ({ id: e.id, name: e.label })),
    { id: "context", name: "Contexte (paramètre dynamique)" },
    { id: "static", name: "Valeur statique" },
  ];

  const fallbackSourceChoices = [
    { id: "client", name: "Client" },
    { id: "aeronef", name: "Aéronef" },
    { id: "siteSettings", name: "SiteSettings" },
    { id: "static", name: "Valeur statique" },
  ];

  const getFieldChoices = (sourceId: string): EntityField[] => {
    return entities.find((e) => e.id === sourceId)?.fields || [];
  };

  return (
    <TabbedForm
      syncWithLocation={false}
      defaultValues={defaultValues ? defaultValues : (record: any) => ({ ...record })}
    >
      <TabbedForm.Tab label="Configuration">
        <TextInput source="name" label="Nom du pattern" validate={required()} fullWidth />
        <TextInput source="code" label="Code technique" validate={required()} fullWidth
          helperText="Identifiant unique du pattern (ex: microtrak_tracking, spider_tracking)" />
        <TextInput source="capability" label="Capability (fonctionnalité)" fullWidth
          helperText="Nom fonctionnel partagé entre patterns (ex: tracking). Le frontend appelle la capability, le moteur résout le bon pattern pour le client." />
        <SelectInput source="requiredModule" label="Module client requis" emptyText="Aucun (optionnel)"
          choices={moduleChoices} fullWidth
          helperText={modulesLoading ? "Chargement..." : "Si défini, le client sera automatiquement associé à ce pattern quand il active le module."} />
        <SelectInput source="method" label="Méthode HTTP" choices={methodChoices} validate={required()} />
        <TextInput source="urlTemplate" label="URL Template" validate={required()} fullWidth
          helperText="Utilisez {{variable}} pour les parties dynamiques" />
        <TextInput source="fallbackUrlTemplate" label="URL de fallback" fullWidth
          helperText="URL alternative si l'URL principale échoue (même format {{variable}})" />
        <TextInput source="contentType" label="Content-Type" fullWidth />
        <TextInput source="cacheTtl" label="Cache (secondes)" fullWidth
          helperText="Durée de cache côté serveur en secondes. 0 ou vide = pas de cache." />
        <TextInput source="description" label="Description" multiline rows={3} fullWidth />
        <BooleanInput source="active" label="Actif" />
      </TabbedForm.Tab>

      <TabbedForm.Tab label="Headers & Params">
        <ArrayInput source="headers" label="Headers HTTP — les valeurs acceptent {{variable}}">
          <SimpleFormIterator inline>
            <TextInput source="name" label="Nom" helperText={false} />
            <TextInput source="value" label="Valeur (ex: Bearer {{apiKey}})" helperText={false} sx={{ minWidth: 300 }} />
          </SimpleFormIterator>
        </ArrayInput>
        <ArrayInput source="queryParams" label="Query Parameters — les valeurs acceptent {{variable}}">
          <SimpleFormIterator inline>
            <TextInput source="name" label="Nom" helperText={false} />
            <TextInput source="value" label="Valeur (ex: {{deviceId}})" helperText={false} sx={{ minWidth: 300 }} />
          </SimpleFormIterator>
        </ArrayInput>
      </TabbedForm.Tab>

      <TabbedForm.Tab label="Body">
        <TextInput source="bodyTemplate" label="Body Template (POST/PUT)" multiline rows={10} fullWidth
          helperText="JSON avec {{variables}}. Ignoré pour GET/DELETE." />
      </TabbedForm.Tab>

      <TabbedForm.Tab label="Variables">
        {error && (
          <Alert severity="warning" sx={{ mb: 2 }}>
            Métadonnées dynamiques indisponibles — liste statique utilisée en secours
          </Alert>
        )}
        <ArrayInput source="variables" label="Variables utilisées dans les templates (URL, headers, body)">
          <SimpleFormIterator>
            <Box sx={{ display: "flex", gap: 1, flexWrap: "wrap", alignItems: "center", width: "100%" }}>
              <TextInput source="variableName" label="Nom (sans {{ }})" helperText={false} sx={{ minWidth: 160 }} />
              {loading ? (
                <CircularProgress size={24} />
              ) : (
                <SelectInput source="source" label="Entité source" helperText={false}
                  choices={entities.length > 0 ? sourceChoices : fallbackSourceChoices} sx={{ minWidth: 180 }} />
              )}
              <FormDataConsumer>
                {({ scopedFormData }) => {
                  const selectedSource = scopedFormData?.source;
                  if (selectedSource === "static") {
                    return (
                      <Typography variant="caption" sx={{ color: "text.secondary", alignSelf: "center", px: 1 }}>
                        → utiliser « Défaut » comme valeur fixe
                      </Typography>
                    );
                  }
                  const fieldChoices = getFieldChoices(selectedSource);
                  if (fieldChoices.length > 0) {
                    return (
                      <SelectInput source="sourceField" label="Champ"
                        choices={fieldChoices} helperText={false} sx={{ minWidth: 220 }} />
                    );
                  }
                  return (
                    <TextInput source="sourceField" label="Champ BDD"
                      helperText={false} sx={{ minWidth: 200 }} />
                  );
                }}
              </FormDataConsumer>
              <TextInput source="defaultValue" label="Défaut" helperText={false} sx={{ minWidth: 140 }} />
              <BooleanInput source="required" label="Requis" helperText={false} />
            </Box>
          </SimpleFormIterator>
        </ArrayInput>
      </TabbedForm.Tab>

      <TabbedForm.Tab label="Mapping réponse">
        <ArrayInput source="responseMappings" label="Transformation de la réponse API vers le modèle interne">
          <SimpleFormIterator inline>
            <TextInput source="internalField" label="Champ interne (ex: tracking.lat)" helperText={false} />
            <TextInput source="externalPath" label="Chemin JSON (ex: data.position.latitude)" helperText={false} sx={{ minWidth: 280 }} />
            <SelectInput source="transformer" label="Cast" choices={transformerChoices} helperText={false} emptyText="Aucun" />
          </SimpleFormIterator>
        </ArrayInput>
      </TabbedForm.Tab>

      <TabbedForm.Tab label="Clients">
        <ReferenceArrayInput source="clients" reference="clients">
          <SelectArrayInput optionText="name" label="Clients associés" fullWidth />
        </ReferenceArrayInput>
      </TabbedForm.Tab>
    </TabbedForm>
  );
};
