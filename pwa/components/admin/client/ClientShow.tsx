import { Show, TabbedShowLayout, TextField, DateField, BooleanField, FunctionField, RichTextField, NumberField } from 'react-admin';
import { ProtectedShowActions } from "../PermissionGuards";
import { getColor } from '../../../app/lib/client';
import { isDefined } from '../../../app/lib/utils';
import { useClient } from '../ClientProvider';

export const ClientShow = () => {

    const { isSuperAdmin: isSuperAdminRole } = useClient();

    const getDescription = ({ address, zipcode, city }) => {
        return <p>{ address }<br/>{ zipcode } - { city }</p>
    };

    const getFilename = path => isDefined(path) ? path.split('/').pop() : '';

    return (
        <Show actions={<ProtectedShowActions />}>
            <TabbedShowLayout>
                <TabbedShowLayout.Tab label="Informations">
                    <TextField source="name" label="Nom"/>
                    <FunctionField 
                        source="address"
                        label="Adresse"
                        render={record => getDescription(record) }
                    />
                    <TextField source="phone" label="N° de téléphone"/>
                    <TextField source="email" label="Adresse email"/>
                    <TextField source="website" label="Site web"/>
                    { isSuperAdminRole
                        ? <TextField source="emailParams" label="Serveur d'email"/>
                        : <TextField source="emailAddressSender" label="Email d'envoi configuré"/>
                    }
                    <TextField source="emailAddressSender" label="Adresse email d'envoi"/>
                    <FunctionField
                        source="countryCode"
                        label="Code pays (TVA)"
                        render={record => {
                            const cc = record.countryCode;
                            if (!cc) return '';
                            return `${cc.code} - ${cc.label}`;
                        }}
                    />
                    <DateField source="minHours" showDate={ false } showTime label="Heure de démarrage"/>
                    <DateField source="maxHours" showDate={ false } showTime label="Heure de fin"/>
                    <DateField source="createdAt" label="Créé le"/>
                    <DateField source="updatedAt" label="Dernière mise à jour, le"/>
                    { isSuperAdminRole && <BooleanField source="active" label="Compte activé" textAlign="center"/> }
                </TabbedShowLayout.Tab>
                { isSuperAdminRole && <TabbedShowLayout.Tab label="Options">
                    <BooleanField source="hasReservation" label="Réservations" textAlign="center"/>
                    <BooleanField source="hasOptions" label="Options" textAlign="center"/>
                    <BooleanField source="hasPartners" label="Partenariat" textAlign="center"/>
                    <BooleanField source="hasOriginContact" label="Origine du contact" textAlign="center"/>
                    <BooleanField source="hasLandingManagement" label="Gestion des atterrissages" textAlign="center"/>
                    <BooleanField source="hasPassengerRegistration" label="Enregistrement des passagers" textAlign="center"/>
                    <BooleanField source="hasMicrotrakTag" label="Balise(s) Microtrak" textAlign="center"/>
                    <BooleanField source="hasWebshop" label="Site e-commerce lié" textAlign="center"/>
                    <BooleanField source="hasIndividualFlightLogs" label="Carnets de vols individuels" textAlign="center"/>
                    <BooleanField source="useAvailabilityFilter" label="Filtrer sur les disponibilités" textAlign="center"/>
                    <BooleanField source="hasPaymentManagement" label="Gestion des paiements" textAlign="center"/>
                    <BooleanField source="hasGifts" label="Gestion des prépaiements" textAlign="center"/>
                    <BooleanField source="hasExpensesManagement" label="Gestion des dépenses" textAlign="center"/>
                    <BooleanField source="hasGroupUpdate" label="Mise à jour des groupes" textAlign="center"/>
                    <BooleanField source="hasNotam" label="NOTAMs / SNOWTAMs" textAlign="center"/>
                    <BooleanField source="hasAI" label="Fonctions IA (Briefing, NOTAM, Kimi)" textAlign="center"/>
                    <BooleanField source="hasCams" label="Caméras Windy" textAlign="center"/>
                    <BooleanField source="hasAiReservationAssistant" label="Assistant IA réservation (email)" textAlign="center"/>
                    <BooleanField source="hasVoiceAssistant" label="Assistant Vocal (téléphone)" textAlign="center"/>
                    <BooleanField source="hasSMS" label="Notifications SMS" textAlign="center"/>
                    <BooleanField source="hasPlanification" label="Planification" textAlign="center"/>
                    <TextField source="smsSenderId" label="Expéditeur SMS"/>
                    <TextField source="assistantCustomInstructions" label="Consignes personnalisées IA"/>
                </TabbedShowLayout.Tab> }
                <TabbedShowLayout.Tab label="Dashboard">
                    <FunctionField 
                        source="color"
                        label="Couleur du Header"
                        render={({ color }) => <span style={{ color }}>{ getColor(color).name }</span> }
                    />
                    <TextField source="timezone" label="Fuseau horaire"/>
                    <FunctionField 
                        source="lat"
                        label="Coordonnées GPS"
                        render={({lat, lng}) => '[' + lat + ', ' + lng + ']'}
                    />
                    <TextField source="zoom" label="Zoom par défaut des cartes"/>
                    <NumberField source="seuilMedical" label="Alerte sur les certificats médicaux (en jours)" />
                    <NumberField source="seuilQualifications" label="Alerte sur les qualifications (en jours)" />
                    <TextField source="thanksTitle" label="Titre du formulaire"/>
                    <TextField source="consentText" label="Texte nécessitant consentement"/>
                    <RichTextField source="thanksMessage" label="Contenu de la page de redirection"/>
                    <BooleanField source="hasPatrolFlight" label="Vol en patrouille" textAlign="center"/>
                    <BooleanField source="hasEmailConfirmation" label="Email de confirmation" textAlign="center"/>
                    <TextField source="confirmationSubject" label="Objet de l'email"/>
                    <RichTextField source="confirmationMessage" label="Contenu de l'email de confirmation"/>
                </TabbedShowLayout.Tab>
                <TabbedShowLayout.Tab label="Images">
                    <FunctionField source="logo" label="Logo" render={({ logo }) => isDefined(logo) ? <a href={logo} target="_blank" rel="noopener noreferrer">{ getFilename(logo) }</a> : ''}/>
                    <FunctionField source="favicon" label="Favicon" render={({ favicon }) => isDefined(favicon) ? <a href={favicon} target="_blank" rel="noopener noreferrer">{ getFilename(favicon) }</a> : ''}/>
                    <FunctionField source="mapIcon" label="Icone représentative sur les cartes" render={({ mapIcon }) => isDefined(mapIcon) ? <a href={mapIcon} target="_blank" rel="noopener noreferrer">{ getFilename(mapIcon) }</a> : ''}/>
                    <FunctionField source="thanksImage" label="Image de la page de remerciement" render={({ thanksImage }) => isDefined(thanksImage) ? <a href={thanksImage} target="_blank" rel="noopener noreferrer">{ getFilename(thanksImage) }</a> : ''}/>
                    <FunctionField 
                        source="pdfBackground" 
                        label="Image de fond du PDF" 
                        render={({ pdfBackground, opacity }) => <p>
                            { isDefined(pdfBackground) ? <a href={pdfBackground} target="_blank" rel="noopener noreferrer">{  getFilename(pdfBackground) }</a> : '' }
                            { isDefined(opacity) && opacity > 0 && <span className="ml-4 text-xs italic text-teal-800">{ "Opacité de " + opacity + " appliquée"}</span> }
                            </p>
                        }
                    />
                </TabbedShowLayout.Tab>
                { isSuperAdminRole && <TabbedShowLayout.Tab label="Abonnement">
                    <FunctionField
                        source="pricingCategory"
                        label="Grille tarifaire"
                        render={record => record.pricingCategory?.name || '—'}
                    />
                    <FunctionField
                        source="modulePacks"
                        label="Packs de modules"
                        render={record => {
                            const packs = record.modulePacks;
                            if (!packs || !packs.length) return '—';
                            return packs.map(p => typeof p === 'object' ? p.name : p).join(', ');
                        }}
                    />
                    <TextField source="subscriptionStatus" label="Statut de l'abonnement"/>
                    <DateField source="trialEndsAt" label="Fin de la période d'essai"/>
                    <NumberField source="maxAeronefs" label="Nombre max d'aéronefs"/>
                    <TextField source="billingCycle" label="Cycle de facturation"/>
                    <NumberField source="annualDiscount" label="Remise annuelle (%)" options={{ style: 'percent', maximumFractionDigits: 0 }}/>
                    <DateField source="nextBillingDate" label="Prochaine facturation"/>
                    <DateField source="lastInvoiceDate" label="Dernière facture"/>
                    <NumberField source="monthlyBasePrice" label="Prix mensuel calculé" options={{ style: 'currency', currency: 'EUR' }}/>
                    <TextField source="odooCustomerId" label="ID client Odoo"/>
                    <TextField source="odooSubscriptionId" label="ID abonnement Odoo"/>
                </TabbedShowLayout.Tab> }
            </TabbedShowLayout>
        </Show>
    )
}
