import { TextInput, FileInput, FileField, NumberInput, BooleanInput, SelectInput, SimpleFormIterator, ArrayInput, TabbedForm, useRedirect, useNotify, TimeInput, ReferenceInput, AutocompleteInput, ReferenceArrayInput, CheckboxGroupInput, DateTimeInput, NumberField, DateInput, useRecordContext, PasswordInput } from "react-admin";
import { Edit } from "react-admin";
import { useFormContext, useWatch } from "react-hook-form";
import { timezones, fileInputSX, uploadImages, sanitizeData } from "../../../app/lib/client";
import { Typography, Divider, Box, Accordion, AccordionSummary, AccordionDetails, Alert, AlertTitle, Button, CircularProgress, Chip, Stack, IconButton, Tooltip, Dialog, DialogTitle, DialogContent, DialogActions } from '@mui/material';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import PhoneIcon from '@mui/icons-material/Phone';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import ErrorOutlineIcon from '@mui/icons-material/ErrorOutline';
import DeleteIcon from '@mui/icons-material/Delete';
import RefreshIcon from '@mui/icons-material/Refresh';
import OpenInNewIcon from '@mui/icons-material/OpenInNew';
import AddIcon from '@mui/icons-material/Add';
import SyncIcon from '@mui/icons-material/Sync';
import SmartToyIcon from '@mui/icons-material/SmartToy';
import MicIcon from '@mui/icons-material/Mic';
import { useState, useEffect, useCallback } from 'react';
import { ColorPreview } from './ColorPreview';
import { ThanksOptions } from './ThanksOptions';
import { useClient } from '../../admin/ClientProvider';
import { useSessionContext } from "../../admin/SessionContextProvider";

const OptionsOverrideWarning = () => {
    const { watch } = useFormContext();
    const pricingCategory = watch("pricingCategory");
    const isCustom = typeof pricingCategory === 'string'
        ? pricingCategory.includes('personnalise')
        : pricingCategory?.slug === 'personnalise';

    if (isCustom) return null;

    return (
        <Alert severity="warning" sx={{ mb: 2 }}>
            Les options ci-dessous seront <strong>écrasées à la sauvegarde</strong> par les packs de modules sélectionnés dans l'onglet Abonnement.
            Pour gérer les options manuellement, passez la grille tarifaire sur <strong>« Personnalisé »</strong>.
        </Alert>
    );
};

const BillingInfoAlert = () => {
    const annualDiscount = useWatch({ name: 'annualDiscount' }) ?? 30;

    return (
        <Alert severity="info" sx={{ mb: 2 }}>
            <AlertTitle>Facturation automatisée via Odoo</AlertTitle>
            <strong>Mensuel</strong> : une facture est générée chaque mois au montant calculé.<br />
            <strong>Annuel</strong> : une seule facture par an avec {annualDiscount}% de remise. Engagement ferme, pas de remboursement anticipé.<br />
            Les factures sont envoyées automatiquement par email. En cas d'impayé, le compte est suspendu après 30 jours.
        </Alert>
    );
};

const API_DOMAIN = process.env.NEXT_PUBLIC_ENTRYPOINT || "";

type CapabilityInfo = {
    capability: string;
    requiredModule: string | null;
    patterns: { id: number; name: string; code: string }[];
};

const IntegrationPatternSelector = () => {
    const { session } = useSessionContext();
    const record = useRecordContext();
    const [capabilities, setCapabilities] = useState<CapabilityInfo[]>([]);
    const [selections, setSelections] = useState<Record<string, number | null>>({});
    const [loading, setLoading] = useState(true);
    const [initialized, setInitialized] = useState(false);

    let formContext: any = null;
    try { formContext = useFormContext(); } catch { /* pas encore dans le form */ }

    useEffect(() => {
        const fetchCapabilities = async () => {
            try {
                const res = await fetch(`${API_DOMAIN}/admin/integrations/capabilities`, {
                    headers: { Authorization: `Bearer ${session?.accessToken}` },
                });
                if (res.ok) setCapabilities(await res.json());
            } catch { /* ignore */ }
            setLoading(false);
        };
        if (session?.accessToken) fetchCapabilities();
    }, [session?.accessToken]);

    useEffect(() => {
        if (!record || !capabilities.length || initialized) return;
        const currentPatterns: any[] = record.integrationPatterns || [];
        const initial: Record<string, number | null> = {};
        for (const cap of capabilities) {
            const match = currentPatterns.find((p: any) => {
                const pid = typeof p === 'object' ? p.id || p['@id'] : p;
                return cap.patterns.some(cp => cp.id === pid || `/integration_patterns/${cp.id}` === pid);
            });
            if (match) {
                const pid = typeof match === 'object' ? match.id || match['@id'] : match;
                const numId = typeof pid === 'string' ? parseInt(pid.replace(/.*\//, '')) : pid;
                initial[cap.capability] = numId;
            } else {
                initial[cap.capability] = null;
            }
        }
        setSelections(initial);
        setInitialized(true);
    }, [record, capabilities, initialized]);

    const handleChange = (capability: string, patternId: number | null) => {
        const updated = { ...selections, [capability]: patternId };
        setSelections(updated);
        if (formContext) {
            const allSelected = Object.entries(updated)
                .filter(([, v]) => v !== null)
                .map(([, v]) => `/integration_patterns/${v}`);
            formContext.setValue('integrationPatterns', allSelected, { shouldDirty: true });
        }
    };

    if (loading || !capabilities.length) return null;

    return (
        <Box sx={{ mt: 3, width: '100%' }}>
            <Divider sx={{ mb: 2, borderBottomWidth: 2, borderColor: '#666' }} />
            <Typography variant="h6" gutterBottom>Intégrations API</Typography>
            <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                Sélectionnez le fournisseur API pour chaque fonctionnalité disponible.
            </Typography>
            {capabilities.map(cap => (
                <Box key={cap.capability} display="flex" gap={2} alignItems="center" sx={{ mb: 1.5 }}>
                    <Typography variant="body2" sx={{ minWidth: 150, fontWeight: 600, textTransform: 'capitalize' }}>
                        {cap.capability}
                    </Typography>
                    <Box component="select"
                        value={selections[cap.capability] ?? ''}
                        onChange={(e: any) => handleChange(cap.capability, e.target.value ? Number(e.target.value) : null)}
                        sx={{
                            minWidth: 250, p: 1, borderRadius: 1,
                            border: '1px solid', borderColor: 'grey.400',
                            fontSize: '0.875rem', bgcolor: 'background.paper',
                        }}
                    >
                        <option value="">— Aucun —</option>
                        {cap.patterns.map(p => (
                            <option key={p.id} value={p.id}>{p.name}</option>
                        ))}
                    </Box>
                    {cap.requiredModule && (
                        <Typography variant="caption" color="text.secondary">
                            Module : {cap.requiredModule}
                        </Typography>
                    )}
                </Box>
            ))}
        </Box>
    );
};

type VapiStatus = {
    configured: boolean;
    assistant_id?: string;
    assistant_name?: string;
    created_at?: string;
    server_url?: string;
    sync_error?: string;
    message?: string;
};

const VapiClientSetup = () => {
    const { session } = useSessionContext();
    const { isSuperAdmin: isSuperAdminRole } = useClient();
    const record = useRecordContext();
    const hasVoiceAssistant = useWatch({ name: "hasVoiceAssistant" });
    const rawId = record?.id;
    const clientId = typeof rawId === "string" ? rawId.replace(/.*\//, "") : rawId;

    const [status, setStatus] = useState<VapiStatus | null>(null);
    const [loading, setLoading] = useState(false);
    const [actionLoading, setActionLoading] = useState<string | null>(null);
    const [feedback, setFeedback] = useState<{ type: "success" | "error"; text: string } | null>(null);

    const headers = useCallback(() => ({
        "Content-Type": "application/json",
        "Authorization": `Bearer ${session?.accessToken}`,
    }), [session?.accessToken]);

    const fetchStatus = useCallback(async () => {
        if (!clientId) return;
        setLoading(true);
        try {
            const res = await fetch(`${API_DOMAIN}/admin/vapi/assistant-status/${clientId}`, { headers: headers() });
            const data = await res.json();
            setStatus(data);
        } catch {
            setStatus(null);
        } finally {
            setLoading(false);
        }
    }, [clientId, headers]);

    useEffect(() => {
        if (hasVoiceAssistant && clientId) fetchStatus();
    }, [hasVoiceAssistant, clientId, fetchStatus]);

    if (!hasVoiceAssistant) return null;

    const handleCreate = async () => {
        setActionLoading("create");
        setFeedback(null);
        try {
            const res = await fetch(`${API_DOMAIN}/admin/vapi/setup-assistant/${clientId}`, {
                method: "POST", headers: headers(),
            });
            const data = await res.json();
            if (data.success) {
                setFeedback({ type: "success", text: data.message });
                await fetchStatus();
            } else {
                setFeedback({ type: "error", text: data.error || "Erreur inconnue" });
            }
        } catch {
            setFeedback({ type: "error", text: "Erreur réseau." });
        } finally {
            setActionLoading(null);
        }
    };

    const handleDelete = async () => {
        if (!confirm("Supprimer l'assistant vocal de ce client ? L'assistant sera supprimé de Vapi.")) return;
        setActionLoading("delete");
        setFeedback(null);
        try {
            const res = await fetch(`${API_DOMAIN}/admin/vapi/delete-assistant/${clientId}`, {
                method: "DELETE", headers: headers(),
            });
            const data = await res.json();
            if (data.success) {
                setFeedback({ type: "success", text: data.message });
                setStatus({ configured: false });
            } else {
                setFeedback({ type: "error", text: data.error || "Erreur inconnue" });
            }
        } catch {
            setFeedback({ type: "error", text: "Erreur réseau." });
        } finally {
            setActionLoading(null);
        }
    };

    const vapiDashboardUrl = status?.assistant_id
        ? `https://dashboard.vapi.ai/assistants/${status.assistant_id}`
        : "https://dashboard.vapi.ai/assistants";

    return (
        <Accordion sx={{ mt: 2, width: "100%" }} defaultExpanded>
            <AccordionSummary expandIcon={<ExpandMoreIcon />}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 1, width: "100%" }}>
                    <PhoneIcon />
                    <Typography fontWeight={600}>Assistant Vocal (Vapi)</Typography>
                    {loading ? (
                        <CircularProgress size={16} sx={{ ml: "auto" }} />
                    ) : status?.configured ? (
                        <Chip label="Actif" color="success" size="small" sx={{ ml: "auto" }} />
                    ) : (
                        <Chip label="Non configuré" color="default" size="small" sx={{ ml: "auto" }} />
                    )}
                </Box>
            </AccordionSummary>
            <AccordionDetails>
                {loading ? (
                    <Box sx={{ display: "flex", justifyContent: "center", py: 3 }}>
                        <CircularProgress />
                    </Box>
                ) : status?.configured ? (
                    <>
                        {status.sync_error ? (
                            <Alert severity="warning" icon={<ErrorOutlineIcon />} sx={{ mb: 2 }}>
                                {status.sync_error}
                            </Alert>
                        ) : (
                            <Box sx={{ mb: 2, p: 2, bgcolor: "grey.50", borderRadius: 1, border: "1px solid", borderColor: "grey.200" }}>
                                <Typography variant="body2" color="text.secondary" gutterBottom>
                                    Détails de l'assistant
                                </Typography>
                                <Box sx={{ display: "grid", gridTemplateColumns: "140px 1fr", gap: 0.5 }}>
                                    <Typography variant="body2" fontWeight={600}>Nom :</Typography>
                                    <Typography variant="body2">{status.assistant_name || "—"}</Typography>
                                    <Typography variant="body2" fontWeight={600}>ID :</Typography>
                                    <Typography variant="body2" sx={{ fontFamily: "monospace", fontSize: "0.8rem" }}>{status.assistant_id}</Typography>
                                    <Typography variant="body2" fontWeight={600}>Créé le :</Typography>
                                    <Typography variant="body2">
                                        {status.created_at ? new Date(status.created_at).toLocaleDateString("fr-FR", { day: "numeric", month: "long", year: "numeric", hour: "2-digit", minute: "2-digit" }) : "—"}
                                    </Typography>
                                    <Typography variant="body2" fontWeight={600}>Webhook :</Typography>
                                    <Typography variant="body2" sx={{ fontFamily: "monospace", fontSize: "0.8rem" }}>{status.server_url || "—"}</Typography>
                                </Box>
                            </Box>
                        )}
                        <Stack direction="row" spacing={1} flexWrap="wrap">
                            <Button
                                variant="contained"
                                size="small"
                                startIcon={actionLoading === "create" ? <CircularProgress size={16} /> : <SyncIcon />}
                                onClick={handleCreate}
                                disabled={!!actionLoading}
                                sx={{ textTransform: "none" }}
                            >
                                Mettre à jour
                            </Button>
                            <Button
                                variant="outlined"
                                size="small"
                                color="error"
                                startIcon={actionLoading === "delete" ? <CircularProgress size={16} /> : <DeleteIcon />}
                                onClick={handleDelete}
                                disabled={!!actionLoading}
                                sx={{ textTransform: "none" }}
                            >
                                Supprimer
                            </Button>
                            <Button
                                variant="outlined"
                                size="small"
                                startIcon={<OpenInNewIcon />}
                                href={vapiDashboardUrl}
                                target="_blank"
                                sx={{ textTransform: "none" }}
                            >
                                Tester sur Vapi.ai
                            </Button>
                            <Tooltip title="Actualiser le statut">
                                <IconButton size="small" onClick={fetchStatus} disabled={!!actionLoading}>
                                    <RefreshIcon fontSize="small" />
                                </IconButton>
                            </Tooltip>
                        </Stack>
                    </>
                ) : (
                    <Box>
                        <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                            Aucun assistant vocal n'est configuré pour ce client. Cliquez ci-dessous pour en créer un sur Vapi.
                        </Typography>
                        <Button
                            variant="contained"
                            startIcon={actionLoading === "create" ? <CircularProgress size={18} /> : <AddIcon />}
                            onClick={handleCreate}
                            disabled={!!actionLoading}
                            sx={{ textTransform: "none" }}
                        >
                            Créer l'assistant vocal
                        </Button>
                    </Box>
                )}
                {feedback && (
                    <Alert severity={feedback.type} sx={{ mt: 2 }}>
                        {feedback.text}
                    </Alert>
                )}
            </AccordionDetails>
        </Accordion>
    );
};

const AiCustomInstructions = () => {
    const { session } = useSessionContext();
    const { isSuperAdmin: isSuperAdminRole } = useClient();
    const record = useRecordContext();
    const notify = useNotify();
    const hasVoiceAssistant = useWatch({ name: "hasVoiceAssistant" });

    const [promptOpen, setPromptOpen] = useState(false);
    const [promptText, setPromptText] = useState('');
    const [promptLoading, setPromptLoading] = useState(false);

    const rawId = record?.id;
    const clientId = typeof rawId === 'string' ? rawId.replace(/.*\//, '') : rawId;

    const handleShowPrompt = async () => {
        setPromptLoading(true);
        try {
            const res = await fetch(`${API_DOMAIN}/admin/vapi/prompt-preview/${clientId}`, {
                headers: { Authorization: `Bearer ${session?.accessToken}` },
            });
            const data = await res.json();
            setPromptText(data.prompt || 'Aucun prompt généré.');
        } catch {
            setPromptText('Erreur lors de la récupération du prompt.');
        }
        setPromptLoading(false);
        setPromptOpen(true);
    };

    const handleUpdateVapi = async () => {
        try {
            const res = await fetch(`${API_DOMAIN}/admin/vapi/setup-assistant/${clientId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Authorization: `Bearer ${session?.accessToken}`,
                },
            });
            const data = await res.json();
            if (data.success) {
                notify('Assistant VAPI mis à jour avec succès', { type: 'success' });
            } else {
                notify(data.error || 'Erreur lors de la mise à jour', { type: 'error' });
            }
        } catch (err: any) {
            notify(err?.message || 'Erreur réseau', { type: 'error' });
        }
    };

    return (
        <Box sx={{ width: '100%', mt: 2 }}>
            <TextInput
                source="assistantCustomInstructions"
                label="Consignes personnalisées pour les assistants IA"
                multiline
                rows={6}
                fullWidth
                helperText="Instructions spécifiques pour les assistants vocal et email (ex: horaires spéciaux, restrictions, promotions en cours...)"
            />
            <Box sx={{ display: 'flex', gap: 1, mt: 1, flexWrap: 'wrap' }}>
                <Button
                    variant="outlined"
                    startIcon={<SmartToyIcon />}
                    onClick={handleShowPrompt}
                    disabled={promptLoading}
                    sx={{ textTransform: 'none' }}
                >
                    {promptLoading ? 'Chargement...' : 'Voir le prompt IA généré'}
                </Button>
                {hasVoiceAssistant && (
                    <Button
                        variant="outlined"
                        color="secondary"
                        startIcon={<MicIcon />}
                        onClick={handleUpdateVapi}
                        sx={{ textTransform: 'none' }}
                    >
                        Mettre à jour l'assistant VAPI
                    </Button>
                )}
            </Box>
            <Dialog open={promptOpen} onClose={() => setPromptOpen(false)} maxWidth="md" fullWidth>
                <DialogTitle>Prompt IA généré pour ce client</DialogTitle>
                <DialogContent>
                    <Typography
                        component="pre"
                        sx={{
                            whiteSpace: 'pre-wrap',
                            fontFamily: 'monospace',
                            fontSize: '0.8rem',
                            background: '#1e293b',
                            color: '#e2e8f0',
                            p: 2,
                            borderRadius: 1,
                            maxHeight: '60vh',
                            overflow: 'auto',
                        }}
                    >
                        {promptText}
                    </Typography>
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setPromptOpen(false)}>Fermer</Button>
                </DialogActions>
            </Dialog>
        </Box>
    );
};

export const ClientsEdit = () => {

    const notify = useNotify();
    const redirect = useRedirect();
    const { updateClient } = useClient();
    const { session } = useSessionContext();
    const { isSuperAdmin: isSuperAdminRole } = useClient();

    const transform = async data => {
        const cachedClient = sessionStorage.getItem("client");
        const previousData = cachedClient ? JSON.parse(cachedClient) : null;

        const sanitizedData = sanitizeData(data, previousData);

        if (sanitizedData.countryCode && typeof sanitizedData.countryCode === 'object') {
            sanitizedData.countryCode = sanitizedData.countryCode['@id'] || null;
        }

        const images = await uploadImages(sanitizedData, session, data.id);
        // @ts-ignore
        const updatedClient = { ...sanitizedData, ...Object.fromEntries(images.map(img => [img.name, img.path || null])) };

        return updatedClient;
    };

    const onSubmit = async (data) => {
        const transformedData = await transform(data);

        try {
            const response = await fetch(`${transformedData['@id']}`, {
                method: 'PUT',
                body: JSON.stringify(transformedData),
                headers: {
                    'Authorization': `Bearer ${session?.accessToken}`,
                    'Content-Type': 'application/json',
                    ...(() => { try { const c = JSON.parse(sessionStorage.getItem('client') || '{}'); return c?.id ? { 'X-Client-Id': String(c.id) } : {}; } catch(e) { return {}; } })()
                }
            });

            if (!response.ok) throw new Error('Erreur lors de la mise à jour');

            const updatedClient = await response.json();

            updateClient(updatedClient);
            notify('Le client a bien été mis à jour.', { type: 'success' });
            if (isSuperAdminRole) {
                redirect('list', 'clients');
            }
        } catch (error) {
            notify('Erreur : ' + error.message, { type: 'error' });
        }
    };

    return (
        // @ts-ignore
        <div style={{ overflowX: 'auto', width: '100%'}}>
            <Edit>
                <TabbedForm
                    onSubmit={ onSubmit }
                    syncWithLocation={false} 
                    defaultValues={(record) => ({
                        hasPassengerRegistration: false,
                        hasOptions: false, 
                        hasPartners: false,
                        hasGifts: false,
                        hasReservation: false,
                        hasLandingManagement: false,
                        hasEmailConfirmation: false,
                        hasPaymentManagement: false,
                        hasMicrotrakTag: false,
                        hasWebshop: false,
                        seuilMedical: 30,
                        seuilQualifications: 30,
                        hasIndividualFlightLogs: false,
                        useAvailabilityFilter: false,
                        hasExpensesManagement: false,
                        hasGroupUpdate: false,
                        hasNotam: false,
                        hasAI: false,
                        hasAiReservationAssistant: false,
                        hasVoiceAssistant: false,
                        hasCams: false,
                        minHours: "00:00",
                        maxHours: "23:59",
                        ...record,
                    })}
                >   
                    <TabbedForm.Tab label="Informations">
                        <TextInput source="name" label="Nom"/>
                        <TextInput source="address" label="Adresse"/>
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1} display="flex" alignItems="center">
                                <TextInput source="zipcode" label="Code postal"/>
                            </Box>
                            <Box flex={2}>
                                <TextInput source="city" label="Ville"/>
                            </Box>
                        </Box>
                        <TextInput source="email" label="Adresse email"/>
                        <TextInput source="phone" label="N° de téléphone"/>
                        <TextInput source="website" label="Site web"/>
                        <PasswordInput source="emailParams" label="Serveur d'email"/>
                        <TextInput source="emailAddressSender" label="Adresse email d'envoi"/>
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                <TimeInput source="minHours" label="Heure de démarrage"/>
                            </Box>
                            <Box flex={1}>
                                <TimeInput source="maxHours" label="Heure de fin"/>
                            </Box>
                        </Box>
                        <ReferenceInput source="countryCode.@id" reference="country_codes" sort={{ field: "code", order: "ASC" }}>
                            <AutocompleteInput
                                optionText={(record: any) => record ? `${record.code} - ${record.label}` : ""}
                                label="Code pays (TVA)"
                                fullWidth
                                helperText="Détermine les taux de TVA applicables"
                            />
                        </ReferenceInput>
                        { isSuperAdminRole && <BooleanInput source="active" label="Utilisateur actif" /> }    
                    </TabbedForm.Tab>
                    { isSuperAdminRole && <TabbedForm.Tab label="Options">
                        <OptionsOverrideWarning />
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                <BooleanInput source="hasReservation" label="Réservations" fullWidth/>
                            </Box>
                            <Box flex={1}>
                                <BooleanInput source="hasOptions" label="Options" fullWidth/>
                            </Box>
                        </Box>
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                <BooleanInput source="hasPartners" label="Partenariat" fullWidth/>
                            </Box>
                            <Box flex={1}>
                                <BooleanInput source="hasOriginContact" label="Origine du contact" fullWidth/>  
                            </Box> 
                        </Box>
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                 <BooleanInput source="hasLandingManagement" label="Gestion des atterrissages" fullWidth/>
                            </Box>
                            <Box flex={1}>
                               <BooleanInput source="hasPassengerRegistration" label="Enregistrement des passagers" fullWidth/>
                            </Box>
                        </Box>
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                <BooleanInput source="hasMicrotrakTag" label="Balise(s) Microtrak" fullWidth/>
                            </Box>
                            <Box flex={1}>
                                <BooleanInput source="hasWebshop" label="Site e-commerce lié" fullWidth/>
                            </Box>
                        </Box>
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                <BooleanInput source="hasIndividualFlightLogs" label="Carnets de vols individuels" fullWidth/>
                            </Box>
                            <Box flex={1}>
                                <BooleanInput source="useAvailabilityFilter" label="Fitrer sur les disponibilités" fullWidth/>
                            </Box>
                        </Box>
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                <BooleanInput source="hasPaymentManagement" label="Gestion des paiements" fullWidth/>
                            </Box>
                            <Box flex={1}>
                                <BooleanInput source="hasGifts" label="Gestion des prépaiements" fullWidth/>
                            </Box>
                        </Box>
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                <BooleanInput source="hasExpensesManagement" label="Gestion des dépenses" fullWidth/>
                            </Box>
                            <Box flex={1}>
                            <BooleanInput source="hasGroupUpdate" label="Mise à jour des groupes" fullWidth/>
                        </Box>
                        </Box>
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                <BooleanInput source="hasNotam" label="NOTAMs / SNOWTAMs" fullWidth/>
                            </Box>
                            <Box flex={1}>
                                <BooleanInput source="hasAI" label="Fonctions IA (Briefing, NOTAM, Kimi)" fullWidth/>
                            </Box>
                        </Box>
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                <BooleanInput source="hasCams" label="Caméras Windy" fullWidth/>
                            </Box>
                            <Box flex={1}>
                                <BooleanInput source="hasAiReservationAssistant" label="Assistant IA réservation (email)" fullWidth/>
                            </Box>
                        </Box>
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                <BooleanInput source="hasVoiceAssistant" label="Assistant Vocal (téléphone)" fullWidth/>
                            </Box>
                            <Box flex={1}/>
                        </Box>
                        <AiCustomInstructions />
                        <VapiClientSetup />
                        <Divider sx={{ mt: 2, borderBottomWidth: 2, borderColor: '#666' }} />
                    </TabbedForm.Tab> }
                    <TabbedForm.Tab label="Dashboard">
                        <ColorPreview/>
                        <SelectInput source="timezone" choices={ timezones }/>   
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                <NumberInput source="lat" label="Latitude" fullWidth />
                            </Box>
                            <Box flex={1}>
                                <NumberInput source="lng" label="Longitude" fullWidth />
                            </Box>
                        </Box>
                        <NumberInput source="zoom" label="Zoom" min={ 1 } max={ 15 }/>
                        <PasswordInput source="trackingApiKey" label="Clé API Tracking (Microtrak)" fullWidth helperText="Clé d'authentification pour l'API de suivi des balises" />
                        <IntegrationPatternSelector />
                        <Typography variant="h6" gutterBottom>
                            Seuils d'alerte
                        </Typography>
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                <NumberInput source="seuilMedical" label="Alerte sur les certificats médicaux" min={ 0 } helperText="Nb de jour(s) avant la fin de validité"/>
                            </Box>
                            <Box flex={1}>
                                <NumberInput source="seuilQualifications" label="Alerte sur les qualifications" min={ 0 } helperText="Nb de jour(s) avant la fin de validité"/>
                            </Box>
                        </Box>
                        <ThanksOptions/>
                    </TabbedForm.Tab>
                    <TabbedForm.Tab label="Images">
                        <FileInput label="Logo" source="logo" accept={{ 'image/png': ['.png'], 'image/jpeg': ['.jpg', '.jpeg'] }} sx={ fileInputSX }
                                    format={(value) => {
                                    if (typeof value === 'string') {
                                        return [{ src: value, title: value.split('/').pop() }];
                                    }
                                    return value;
                                }}
                        >
                            <FileField source="src" title="title" />
                        </FileInput> 
                        <FileInput label="Icone GPS" source="mapIcon" accept={{ 'image/png': ['.png'], 'image/jpeg': ['.jpg', '.jpeg'] }} sx={ fileInputSX }
                                    format={(value) => {
                                    if (typeof value === 'string') {
                                        return [{ src: value, title: value.split('/').pop() }];
                                    }
                                    return value;
                                }}
                        >
                            <FileField source="src" title="title" />
                        </FileInput> 
                        <FileInput label="Favicon" source="favicon" accept={{ 'image/png': ['.png'], 'image/jpeg': ['.jpg', '.jpeg'] }} sx={ fileInputSX }
                                    format={(value) => {
                                    if (typeof value === 'string') {
                                        return [{ src: value, title: value.split('/').pop() }];
                                    }
                                    return value;
                                }}
                        >
                            <FileField source="src" title="title" />
                        </FileInput>
                        <FileInput label="Image de la page de remerciement" source="thanksImage" accept={{ 'image/png': ['.png'], 'image/jpeg': ['.jpg', '.jpeg'] }} sx={ fileInputSX }
                                    format={(value) => {
                                    if (typeof value === 'string') {
                                        return [{ src: value, title: value.split('/').pop() }];
                                    }
                                    return value;
                                }}
                        >
                            <FileField source="src" title="title" />
                        </FileInput>
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={2}>
                                <FileInput label="Arrière plan PDF" source="pdfBackground" accept={{ 'image/png': ['.png'], 'image/jpeg': ['.jpg', '.jpeg'] }} sx={ fileInputSX }
                                            format={(value) => {
                                            if (typeof value === 'string') {
                                                return [{ src: value, title: value.split('/').pop() }];
                                            }
                                            return value;
                                        }}
                                >
                                    <FileField source="src" title="title" />
                                </FileInput>
                            </Box>
                            <Box flex={1} display="flex" alignItems="center" pt={2}>
                                <NumberInput source="opacity" label="Opacité" min={ 0 } max={ 1 } fullWidth />
                            </Box>
                        </Box>
                    </TabbedForm.Tab>
                    { isSuperAdminRole && <TabbedForm.Tab label="Abonnement">
                        <ReferenceInput source="pricingCategory" reference="pricing-categories">
                            <AutocompleteInput optionText="name" label="Grille tarifaire" fullWidth />
                        </ReferenceInput>
                        <Alert severity="info" sx={{ mb: 2 }}>
                            Les packs de modules ci-dessous définissent automatiquement les options du client à la sauvegarde.
                            Pour gérer les options manuellement, sélectionnez la grille tarifaire <strong>« Personnalisé »</strong>.
                        </Alert>
                        <ReferenceArrayInput source="modulePacks" reference="module-packs">
                            <CheckboxGroupInput optionText="name" label="Packs de modules" />
                        </ReferenceArrayInput>
                        <SelectInput source="subscriptionStatus" label="Statut de l'abonnement" choices={[
                            { id: "trial", name: "Essai" },
                            { id: "active", name: "Actif" },
                            { id: "suspended", name: "Suspendu" },
                            { id: "cancelled", name: "Annulé" },
                        ]} />
                        <DateTimeInput source="trialEndsAt" label="Fin de la période d'essai" />
                        <NumberInput source="maxAeronefs" label="Nombre max d'aéronefs" />
                        <Divider sx={{ mt: 2, mb: 2, borderBottomWidth: 2, borderColor: '#666' }} />
                        <Typography variant="h6" gutterBottom>
                            Facturation
                        </Typography>
                        <SelectInput
                            source="billingCycle"
                            label="Cycle de facturation"
                            choices={[
                                { id: 'monthly', name: 'Mensuel' },
                                { id: 'annual', name: 'Annuel (-30%)' },
                            ]}
                            defaultValue="monthly"
                        />
                        <NumberInput
                            source="annualDiscount"
                            label="Remise annuelle (%)"
                            min={0}
                            max={100}
                            step={5}
                            defaultValue={30}
                            helperText="Remise appliquée sur le tarif annuel (par défaut 30%)"
                        />
                        <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
                            <Box flex={1}>
                                <DateInput
                                    source="nextBillingDate"
                                    label="Prochaine facturation"
                                    fullWidth
                                />
                            </Box>
                            <Box flex={1}>
                                <DateInput
                                    source="lastInvoiceDate"
                                    label="Dernière facture"
                                    disabled
                                    fullWidth
                                />
                            </Box>
                        </Box>
                        <NumberInput
                            source="monthlyBasePrice"
                            label="Prix mensuel calculé (€)"
                            disabled
                            helperText="Calculé automatiquement à partir de la grille et des modules"
                        />
                        <BillingInfoAlert />
                        <Accordion sx={{ mt: 3, width: "100%" }}>
                            <AccordionSummary expandIcon={<ExpandMoreIcon />}>
                                <Typography>Odoo (Phase 3)</Typography>
                            </AccordionSummary>
                            <AccordionDetails>
                                <TextInput source="odooCustomerId" label="ID client Odoo" fullWidth />
                                <TextInput source="odooSubscriptionId" label="ID abonnement Odoo" fullWidth />
                            </AccordionDetails>
                        </Accordion>
                    </TabbedForm.Tab> }
                </TabbedForm>
            </Edit>
        </div>
    )
};