import { Show, SimpleShowLayout, TextField, NumberField, DateField, FunctionField, ArrayField, Datagrid, FileField, BooleanField } from 'react-admin';
import { ProtectedShowActions } from "../PermissionGuards";
import { getShipStyle, isDefined, isNotBlank } from '../../../app/lib/utils';
import { paymentMode } from '../../../app/lib/client';
import Chip from '@mui/material/Chip';
import { SingleDocumentField } from "../shared/OdooDocumentField";

export const ExpenseShow = () => {

    const getChipMode = mode => {
    const modeWithColor = paymentMode.find(p => p.id === mode);
    // @ts-ignore
    return <Chip label={mode.toUpperCase()} size="small" sx={ getShipStyle(modeWithColor) }/>
    };

    return (
        <Show actions={<ProtectedShowActions />}>
            <SimpleShowLayout>
                <DateField source="date" label="Date" sortable={ true } />
                <FunctionField
                    source="beneficiaire"
                    label="Bénéficiaire"
                    render={record => record?.beneficiaire ?? '' }
                />
                <TextField source="libelle" label="Libellé"/>
                <ArrayField source="details">
                    <Datagrid
                        optimized
                        bulkActionButtons={false}
                        sx={{
                            '& .RaDatagrid-headerCell': { backgroundColor: '#ededed', fontWeight: 'lighter' },
                            '& .RaDatagrid-rowCell': { verticalAlign: 'top' },
                        }}
                    >
                        <FunctionField
                            source="mode"
                            label="Mode de paiement"
                            render={({mode}) => isDefined(mode) ? getChipMode(mode) : ''}
                        />
                        <FunctionField
                            source="amount"
                            label="Montant TTC"
                            render={({ amount }) => amount.toFixed(2) + " €" }
                        />
                        <FunctionField
                            source="tauxTva"
                            label="TVA"
                            render={({ tauxTva }) => tauxTva != null ? `${(tauxTva * 100).toFixed(1)} %` : '—'}
                        />
                        <FunctionField
                            source="amountHT"
                            label="Montant HT"
                            render={({ amountHT }) => amountHT != null ? amountHT.toFixed(2) + " €" : '—'}
                        />
                    </Datagrid>
                </ArrayField>
                <FunctionField
                    source="totalTTC"
                    label="Total TTC"
                    render={({ totalTTC }) => isDefined(totalTTC) ? totalTTC.toFixed(2) + " €" : '' }
                />
                <FunctionField
                    label="Total TVA"
                    render={({ details }) => {
                        if (!details?.length) return '—';
                        const totalTva = details.reduce((sum, d) => {
                            const amt = d.amount ?? 0;
                            const tva = d.tauxTva ?? 0;
                            return sum + (tva > 0 ? amt - amt / (1 + tva) : 0);
                        }, 0);
                        return totalTva > 0 ? totalTva.toFixed(2) + ' €' : '—';
                    }}
                />
                <FunctionField
                    source="totalHT"
                    label="Total HT"
                    render={({ totalHT }) => isDefined(totalHT) ? totalHT.toFixed(2) + ' €' : '' }
                />
                <SingleDocumentField source="document" label="Justificatif"/>
                <BooleanField source="relatedToMaintenance" label="Associé à un entretien"/>
            </SimpleShowLayout>
        </Show>
    )
}