import { Show, SimpleShowLayout, TextField, DateField, FunctionField, BooleanField, NumberField, ArrayField, Datagrid } from 'react-admin';
import { ProtectedShowActions } from "../PermissionGuards";

export const CadeauShow = () => (
    <Show title="Détail du prépaiement" actions={<ProtectedShowActions />}>
        <SimpleShowLayout>
            <TextField source="code" label="N° de bon"/>
            <DateField source="date" label="Date d'achat"/>
            <DateField source="fin" label="Date d'expiration"/>
            <TextField source="beneficiaire" label="Bénéficiaire"/>
            <TextField source="offreur" label="Payeur"/>
            <TextField source="email" label="Adresse email"/>
            <TextField source="telephone" label="N° de téléphone"/>
            <NumberField source="quantite" label="Quantité"/>
            <FunctionField
                    source="circuit.code"
                    label="Circuit"
                    render={record => record.circuit ? <>{record.circuit.code}<span className="text-xs italic">{' — '}</span>{record.circuit.nom}</> : '—'}
                    textAlign="right"
                />
            <FunctionField
                label="Options"
                render={({selectedOptions, option, options}) => {
                    if (Array.isArray(selectedOptions) && selectedOptions.length > 0) {
                        return <>{selectedOptions.map(o => o?.nom).filter(Boolean).join(', ')}</>;
                    }
                    return <>{option?.nom ?? ''}{options?.nom ?? ''}</>;
                }}
            />
            <ArrayField source="origine" label="Origine de l'appel">
                    <Datagrid isRowSelectable={ record => false } rowClick={ false } bulkActionButtons={false} sx={{ '& .RaDatagrid-headerCell': {display: 'none'}}} className="text-xs italic">
                        <TextField source="name" label="Nom"/>
                    </Datagrid>
                </ArrayField>
            <NumberField source="prix" label="Prix TTC" options={{ style: 'currency', currency: 'EUR' }}/>
            <FunctionField
                source="tauxTva"
                label="TVA"
                render={record => record.tauxTva != null ? `${(record.tauxTva * 100).toFixed(1)} %` : '—'}
            />
            <NumberField source="prixHT" label="Prix HT" options={{ style: 'currency', currency: 'EUR' }}/>
            <NumberField source="cout" label="Coût" options={{ style: 'currency', currency: 'EUR' }}/>
            <TextField source="message" />
            <TextField source="paymentId" label="N° du paiement"/>
            <BooleanField source="used" label="Utilisé"/>
        </SimpleShowLayout>
    </Show>
)
