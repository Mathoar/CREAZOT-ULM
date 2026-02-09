import { Show, SimpleShowLayout, TextField, DateField, FunctionField, BooleanField, ArrayField, Datagrid, NumberField } from 'react-admin';
import { paymentMode } from '../../../app/lib/client';
import { Chip } from '@mui/material';
import { getShipStyle } from '../../../app/lib/utils';

export const CadeauShow = () => {

    const getChipMode = mode => {
        const modeWithColor = paymentMode.find(p => p.id === mode);
        // @ts-ignore
        return <Chip label={mode.toUpperCase()} size="small" sx={ getShipStyle(modeWithColor) }/>
    };

    return (
        <Show title="Détail du prépaiement">
            <SimpleShowLayout>
                <TextField source="code" label="N° de bon"/>
                <DateField source="date" label="Date d'achat"/>
                <DateField source="fin" label="Date d'expiration"/>
                <TextField source="beneficiaire" label="Bénéficiaire"/>
                <TextField source="offreur" label="Payeur"/>
                <TextField source="email" label="Adresse email"/>
                <TextField source="telephone" label="N° de téléphone"/>
                <FunctionField
                        source="circuit.code"
                        label="Circuit"
                        render={record => <>{record.quantite}x {record.circuit.code}<span className="text-xs italic">{'-'}</span> { record.circuit.nom }</> }
                        textAlign="right"
                    />
                <FunctionField
                    source="options"
                    label="Options"
                    render={({option, options}) => <>{option?.nom ?? ''}{options?.nom ?? ''}</> }
                />
                <ArrayField source="origine" label="Origine de l'appel">
                        <Datagrid isRowSelectable={ record => false } rowClick={ false } bulkActionButtons={false} sx={{ '& .RaDatagrid-headerCell': {display: 'none'}}} className="text-xs italic">
                            <TextField source="name" label="Nom"/>
                        </Datagrid>
                    </ArrayField>
                <TextField source="message" />
                <TextField source="paymentId" label="Id du paiement"/>
                <NumberField source="prix" label="Prix" options={{ style: 'currency', currency: 'EUR' }}/>
                <ArrayField source="details">
                    <Datagrid
                        optimized
                        rowClick={false}
                        bulkActionButtons={false}
                        sx={{
                            '& .RaDatagrid-headerCell': { backgroundColor: '#ededed', fontWeight: 'lighter' },
                            '& .RaDatagrid-rowCell': { verticalAlign: 'top' },
                        }}
                    >
                        <FunctionField
                            source="mode"
                            label="Mode de paiement"
                            render={({mode}) => getChipMode(mode)}
                        />
                        <FunctionField
                            source="amount"
                            label="Montant (€)"
                            render={({ amount }) => (amount ?? 0).toFixed(2) + "€" }
                        />
                    </Datagrid>
                </ArrayField>
                <BooleanField source="used" label="utilisé"/>
            </SimpleShowLayout>
        </Show>
    );
}