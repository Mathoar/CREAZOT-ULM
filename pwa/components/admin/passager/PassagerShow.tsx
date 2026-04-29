import { Show, SimpleShowLayout, TextField, DateField, EmailField, FunctionField, NumberField, useGetList } from 'react-admin';
import { ProtectedShowActions } from "../PermissionGuards";
import ClearIcon from '@mui/icons-material/Clear';
import DoneIcon from '@mui/icons-material/Done';
import WarningAmberIcon from '@mui/icons-material/WarningAmber';
import { Alert, Chip } from '@mui/material';
import { isDefined } from '../../../app/lib/utils';
import { useMemo } from 'react';

export const PassagerShow = () => {

    const { data: flightRules } = useGetList('flight_rules', { pagination: { page: 1, perPage: 50 } });
    const poidsMax = useMemo(() => {
        if (!flightRules?.length) return null;
        const limits = flightRules.map(r => r.poidsMaxPassager).filter(Boolean) as number[];
        return limits.length > 0 ? Math.min(...limits) : null;
    }, [flightRules]);

    const getConsentIcon = ({ consentAccepted }) => {
        return isDefined(consentAccepted) ? 
        consentAccepted ? 
            <DoneIcon className="text-green-500"/> : 
            <ClearIcon className="text-red-500"/> :
        <></>
    };

    return (
        <Show actions={<ProtectedShowActions />}>
            <SimpleShowLayout>
                <DateField source="date" label="Date" sortable={ true } />
                <TextField source="nom" label="Nom" sortable={ true }/>
                <TextField source="prenom" label="Prénom" sortable={ true }/>
                <TextField source="telephone" label="Téléphone" sortable={ true }/>
                <EmailField source="email" label="Adresse email"/>
                <FunctionField
                    label="Poids"
                    render={record => {
                        if (!record?.poids) return '—';
                        const depasse = poidsMax && record.poids > poidsMax;
                        if (depasse) {
                            return (
                                <>
                                    <Chip icon={<WarningAmberIcon />} label={`${record.poids} kg`} color="error" size="small" variant="outlined" />
                                    <Alert severity="warning" sx={{ mt: 1 }}>
                                        Le poids déclaré ({record.poids} kg) dépasse la limite de {poidsMax} kg définie dans les règles de vol.
                                    </Alert>
                                </>
                            );
                        }
                        return `${record.poids} kg`;
                    }}
                />
                <FunctionField 
                    source="consentAccepted"
                    label="Consentement"
                    render={record => getConsentIcon(record) }
                    textAlign="center"
                />
                <DateField source="consentDatetime" locales="fr-FR" showTime showDate={ false } label="Accepté à"/>
                <TextField source="consentText" label="Texte accepté"/>
            </SimpleShowLayout>
        </Show>
    );
}