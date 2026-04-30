import { Show, SimpleShowLayout, TextField, DateField, NumberField, Datagrid, ArrayField, FunctionField, useNotify, useRefresh, useRecordContext, TopToolbar, EditButton, DeleteButton, Button as ReactAdminButton } from 'react-admin';
import { usePermissions } from "../PermissionProvider";
import { isDefined } from '../../../app/lib/utils';
import { useClient } from '../ClientProvider';
import { clientWithOptions } from "../../../app/lib/client";
import { FC, useState } from 'react';
import { Button as MuiButton, Dialog, DialogTitle, DialogContent, DialogActions, TextField as MuiTextField, Typography, Alert } from '@mui/material';
import BuildIcon from '@mui/icons-material/Build';
import { useSession } from "next-auth/react";

const API_DOMAIN = process.env.NEXT_PUBLIC_API_DOMAIN || '';

const LandingDetails: FC = () => {
    const record = useRecordContext();
    if (!record || !record.landings || record.landings.length === 0) return null;

    return (
        <div className="p-2">
            <p className="font-semibold mb-2 text-sm text-gray-700">✈️ Atterrissages</p>
            <Datagrid
                isRowSelectable={record => false} rowClick={false} bulkActionButtons={false} sx={{ '& .RaDatagrid-headerCell': { backgroundColor: '#ededed', fontWeight: "lighter" } }} className="text-xs italic"
                data={record.landings}
                total={record.landings.length}
            >
                <FunctionField
                    source="airportCode"
                    label="Aéroport"
                    render={record => <p><span className="text-xs italic">{record.airportName}</span></p>}
                />
                <NumberField source="complets" label="Complet(s)" />
                <NumberField source="touches" label="Touché(s)" />
            </Datagrid>
        </div>
    );
};

const CorrectHorametreButton = () => {
    const record = useRecordContext();
    const notify = useNotify();
    const refresh = useRefresh();
    const { client } = useClient();
    const { data: session } = useSession() as any;
    const [open, setOpen] = useState(false);
    const [value, setValue] = useState('');
    const [loading, setLoading] = useState(false);

    if (!record) return null;

    const isDecimal = record.aeronef?.decimal;

    const formatHorametre = (val: number) => {
        const hours = Math.trunc(val);
        const minutes = Math.round((val - Math.trunc(val)) * (isDecimal ? 10 : 100));
        return `${hours}${isDecimal ? ',' : ':'}${!isDecimal && minutes < 10 ? '0' : ''}${minutes}`;
    };

    const handleOpen = () => {
        setValue(String(record.horametreFin));
        setOpen(true);
    };

    const handleSubmit = async () => {
        const parsed = parseFloat(value.replace(',', '.').replace(':', '.'));
        if (isNaN(parsed) || parsed <= record.horametreDepart) {
            notify("L'horamètre de fin doit être supérieur à l'horamètre de départ", { type: 'error' });
            return;
        }

        setLoading(true);
        try {
            const headers: Record<string, string> = {
                'Content-Type': 'application/json',
                Authorization: `Bearer ${session?.accessToken}`,
            };
            if (client?.id) headers['X-Client-Id'] = String(client.id);

            const prestationId = typeof record.id === 'string' && record.id.includes('/')
                ? record.id.split('/').pop()
                : record.id;

            const response = await fetch(`${API_DOMAIN}/admin/prestation/${prestationId}/correct-horametre`, {
                method: 'POST',
                headers,
                body: JSON.stringify({ horametreFin: parsed }),
            });

            if (!response.ok) {
                const err = await response.json();
                throw new Error(err.error || 'Erreur serveur');
            }

            notify('Horamètre corrigé avec succès (prestation, durée et aéronef mis à jour)', { type: 'success' });
            setOpen(false);
            refresh();
        } catch (e: any) {
            notify(e.message || 'Erreur lors de la correction', { type: 'error' });
        } finally {
            setLoading(false);
        }
    };

    return (
        <>
            <ReactAdminButton label="Corriger l'horamètre" onClick={handleOpen}>
                <BuildIcon />
            </ReactAdminButton>
            <Dialog open={open} onClose={() => setOpen(false)} maxWidth="xs" fullWidth>
                <DialogTitle>Correction de l'horamètre de fin</DialogTitle>
                <DialogContent>
                    <Alert severity="info" sx={{ mb: 2 }}>
                        Cette action met à jour automatiquement la durée de la prestation et l'horamètre de l'aéronef.
                    </Alert>
                    <Typography variant="body2" sx={{ mb: 1 }}>
                        Horamètre de départ : <strong>{formatHorametre(record.horametreDepart)}</strong>
                    </Typography>
                    <Typography variant="body2" sx={{ mb: 2 }}>
                        Horamètre de fin actuel : <strong>{formatHorametre(record.horametreFin)}</strong>
                    </Typography>
                    <MuiTextField
                        label="Nouvel horamètre de fin"
                        value={value}
                        onChange={(e) => setValue(e.target.value)}
                        fullWidth
                        autoFocus
                        helperText={isDecimal ? "Format décimal (ex: 1234,5)" : "Format conventionnel (ex: 1234.30)"}
                    />
                </DialogContent>
                <DialogActions>
                    <MuiButton onClick={() => setOpen(false)} disabled={loading}>Annuler</MuiButton>
                    <MuiButton onClick={handleSubmit} variant="contained" disabled={loading}>
                        {loading ? 'Correction...' : 'Corriger'}
                    </MuiButton>
                </DialogActions>
            </Dialog>
        </>
    );
};

const PrestationShowActions = () => {
    const { canWrite } = usePermissions();
    return (
        <TopToolbar>
            {canWrite('vols') && <EditButton />}
            {canWrite('vols') && <CorrectHorametreButton />}
            {canWrite('vols') && (
                <DeleteButton
                    mutationMode="pessimistic"
                    confirmTitle="Supprimer cette prestation ?"
                    confirmContent="L'horamètre de l'aéronef et les heures de vol du pilote seront automatiquement corrigés."
                />
            )}
        </TopToolbar>
    );
};

export const PrestationShow = () => {

    const { client } = useClient();

    const getFormattedDuration = ({ aeronef, duree }) => {
        const hours = Math.trunc(duree);
        const minutes = aeronef.decimal ? Math.round((duree - Math.trunc(duree)) * 60) : Math.round((duree - Math.trunc(duree)) * 100);
        return `${ hours }:${ minutes < 10 ? '0' : '' }${ minutes }`;
    }
    
    const getFormattedHorametre = (prestation, horametre) => {
        const hours = Math.trunc(prestation[horametre]);
        const minutes = Math.round((prestation[horametre] - Math.trunc(prestation[horametre])) * (prestation.aeronef.decimal ? 10 : 100));
        return `${ hours }${prestation.aeronef.decimal ? ',' : ':'}${ !prestation.aeronef.decimal && minutes < 10 ? '0' : '' }${ minutes }`;
    }

    const OptionField = () => {
        return !clientWithOptions(client) ? null :
            <TextField source="option.nom" label="Option"/>
    };

    return (
        <Show actions={<PrestationShowActions />}>
            <SimpleShowLayout>
                <DateField source="date" label="Date"/>
                <TextField source="aeronef.immatriculation" label="Aéronef"/>
                <FunctionField
                    label="Pilote"
                    source="pilote.firstName"
                    render={(record) => isDefined(record.pilote) && isDefined(record.pilote.firstName) ?
                        record.pilote.firstName.charAt(0).toUpperCase() + record.pilote.firstName.slice(1) : ''
                    }                     
                />
                <FunctionField
                    label="Encadrant"
                    source="encadrant.firstName"
                    render={(record) => isDefined(record.encadrant) && isDefined(record.encadrant.firstName) ?
                        record.encadrant.firstName.charAt(0).toUpperCase() + record.encadrant.firstName.slice(1) : ''
                    }                     
                />
                <FunctionField
                    source="horametreDepart"
                    label="Horamètre au Départ"
                    render={record => getFormattedHorametre(record, "horametreDepart")}
                    textAlign="right"
                />
                <FunctionField
                    source="duree"
                    label="Durée"
                    render={record => getFormattedDuration(record)}
                    textAlign="right"
                />
                <FunctionField
                    source="horametreFin"
                    label="Horamètre à l'arrivée"
                    render={record => getFormattedHorametre(record, "horametreFin")}
                    textAlign="right"
                />
                <ArrayField source="vols">
                    <Datagrid
                        optimized
                        expand={<LandingDetails />}
                        bulkActionButtons={false}
                        sx={{
                            '& .RaDatagrid-headerCell': { backgroundColor: '#ededed', fontWeight: 'lighter' },
                            '& .RaDatagrid-rowCell': { verticalAlign: 'top' },
                        }}
                    >
                        <NumberField source="quantite" label="Nb vol(s)" />
                        <FunctionField
                            source="circuit"
                            render={record =>
                                isDefined(record.circuit) && isDefined(record.circuit.code) && isDefined(record.circuit.nom) ?
                                <p>{record.circuit.code} - <span className="text-xs italic">{record.circuit.nom}</span></p> : ""
                            }
                        />
                        <FunctionField
                            source="nature"
                            render={record =>
                                isDefined(record.circuit) && isDefined(record.circuit.nature) && isDefined(record.circuit.nature.code) && isDefined(record.circuit.nature.label) ?
                                <p>{record.circuit.nature.code} - <span className="text-xs italic">{record.circuit.nature.label}</span></p> : ""
                            }
                        />
                        <OptionField />
                        <FunctionField
                            source="prix"
                            label="Prix TTC"
                            render={record => record.prix != null ? record.prix.toFixed(2) + ' €' : '—'}
                        />
                        <FunctionField
                            source="tauxTva"
                            label="TVA"
                            render={record => record.tauxTva != null ? `${(record.tauxTva * 100).toFixed(1)} %` : '—'}
                        />
                        <FunctionField
                            source="prixHT"
                            label="Prix HT"
                            render={record => record.prixHT != null ? record.prixHT.toFixed(2) + ' €' : '—'}
                        />
                    </Datagrid>
                </ArrayField>
                <TextField source="remarques" label="Remarques"/>
                <NumberField source="turnover" label="C.A." options={{ style: 'currency', currency: 'EUR' }}/>
                <DateField source="createdAt" label="Créé le" showTime/>
                <FunctionField
                    label="Créé par"
                    source="createdBy.firstName"
                    render={(record) => isDefined(record?.createdBy) && isDefined(record?.createdBy?.firstName) ?
                        record?.createdBy?.firstName?.charAt(0).toUpperCase() + record?.createdBy?.firstName?.slice(1) : ''
                    }
                />
                <DateField source="updatedAt" label="Modifié le" showTime/>
                <FunctionField
                    label="Modifié par"
                    source="updatedBy.firstName"
                    render={(record) => isDefined(record?.updatedBy) && isDefined(record?.updatedBy?.firstName) ?
                        record?.updatedBy?.firstName?.charAt(0).toUpperCase() + record?.updatedBy?.firstName?.slice(1) : ''
                    }
                />
            </SimpleShowLayout>
        </Show>
    );
}