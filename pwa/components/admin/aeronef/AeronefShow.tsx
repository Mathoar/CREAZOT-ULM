import { Show, SimpleShowLayout, TextField, NumberField, BooleanField, FunctionField, DateField, FileField } from 'react-admin';
import { ProtectedShowActions } from "../PermissionGuards";
import { isDefined } from '../../../app/lib/utils';
import { DocumentListField } from "../shared/OdooDocumentField";
import { Divider, Typography } from '@mui/material';

export const AeronefShow = () => {

    const getDecimalTimeFromLocale = timeToFormat => Math.trunc(timeToFormat) + (timeToFormat - Math.trunc(timeToFormat)) / 60 * 100;

    const getRemainingTime = record => record.decimal ? getRemainingDecimalTime(record) : getRemainingLocaleTime(record);

    const getRemainingMotorTime = record => record.decimal ? getRemainingDecimalTime({entretien: record.changementMoteur, horametre: record.horametre, seuilAlerte: record.seuilAlerteChangementMoteur}) : getRemainingMotorLocaleTime(record);

    const getRemainingLocaleTime = ({entretien, horametre, seuilAlerte}) => getRemainingDecimalTime({entretien : getDecimalTimeFromLocale(entretien), horametre: getDecimalTimeFromLocale(horametre), seuilAlerte});

    const getRemainingMotorLocaleTime = ({changementMoteur, horametre, seuilAlerteChangementMoteur}) => getRemainingDecimalTime({entretien : getDecimalTimeFromLocale(changementMoteur), horametre: getDecimalTimeFromLocale(horametre), seuilAlerte: seuilAlerteChangementMoteur});

    const getRemainingDecimalTime = ({entretien, horametre, seuilAlerte}) => {
        const alerte = isDefined(seuilAlerte) ? seuilAlerte : 10;
        const remainingDecimalTime = entretien - horametre;
        const sign = remainingDecimalTime > 0 ? "" : "+ ";
        const intRemainingTime = Math.abs(Math.trunc(remainingDecimalTime));
        const rest = Math.round((Math.abs(remainingDecimalTime) - intRemainingTime) * 60);
        const formattedRest = rest < 10 ? "0" + rest.toFixed(0) : rest.toFixed(0);
        return (
            <p className={`${ (entretien - alerte) - horametre < 0 ? (horametre > entretien ? 'text-red-500' : 'text-orange-500') : 'text-green-500'}`}>
                { sign + intRemainingTime + "h" + formattedRest }
            </p>
        );
    };

    const formatParachuteEcheance = (record: any) => {
        if (!record?.hasParachute) return null;
        if (!record.dateReconditionnementParachute || !record.periodiciteParachuteMois) return 'Dates non renseignées';
        const date = new Date(record.dateReconditionnementParachute);
        date.setMonth(date.getMonth() + record.periodiciteParachuteMois);
        const now = new Date();
        const diff = Math.floor((date.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
        const echeance = date.toLocaleDateString('fr-FR');
        if (diff < 0) return <span className="text-red-500 font-bold">Dépassée ({echeance}, {Math.abs(diff)}j de retard)</span>;
        if (diff < 180) return <span className="text-orange-500 font-bold">{echeance} ({diff}j restants)</span>;
        return <span className="text-green-500">{echeance} ({diff}j restants)</span>;
    };

    return (
        <Show actions={<ProtectedShowActions />}>
            <SimpleShowLayout>
                <TextField source="immatriculationComplete" label="Immatriculation" />
                <TextField source="immatriculation" label="Identifiant radio" />
                <TextField source="modele" label="Modèle" />
                <NumberField source="horametre" options={{ style: 'unit', unit: 'hour' }} label="Horamètre"/>
                <NumberField source="entretien" options={{ style: 'unit', unit: 'hour' }} label="Prochain entretien"/>
                <FunctionField
                    source="entretien"
                    label="Temps de vol avant le prochain entretien"
                    render={ record => <>{ getRemainingTime(record) }</> }
                />
                <FunctionField
                    source="changementMoteur"
                    label="Temps de vol avant le prochain changement moteur"
                    render={ record => <>{ getRemainingMotorTime(record) }</> }
                />   
                <NumberField source="seuilAlerte" options={{ style: 'unit', unit: 'hour' }} label="Seuil d'alerte (en h) avant entretien"/>
                <NumberField source="seuilAlerteChangementMoteur" options={{ style: 'unit', unit: 'hour' }} label="Seuil d'alerte (en h) avant changement du moteur"/>
                <TextField source="codeBalise" label="Code balise"/>
                <TextField source="typeBalise" label="Type de balise / dispositif" />
                <Divider sx={{ width: '100%' }} />
                <Typography variant="subtitle2">Parachute de récupération</Typography>
                <BooleanField source="hasParachute" label="Équipé d'un parachute"/>
                <FunctionField
                    label="Échéance reconditionnement"
                    render={record => record?.hasParachute ? formatParachuteEcheance(record) : '—'}
                />
                <FunctionField
                    label="Dernier reconditionnement"
                    render={record => record?.dateReconditionnementParachute
                        ? new Date(record.dateReconditionnementParachute).toLocaleDateString('fr-FR')
                        : '—'}
                />
                <FunctionField
                    label="Périodicité"
                    render={record => record?.periodiciteParachuteMois ? `${record.periodiciteParachuteMois} mois` : '—'}
                />
                <Divider sx={{ width: '100%' }} />
                <BooleanField source="decimal" label="Horamètre décimal"/>
                <BooleanField source="isAvailable" label="Disponible"/>
                <DocumentListField source="documents" label="Documents associés"/>
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