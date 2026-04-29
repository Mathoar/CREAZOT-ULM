import { Show, SimpleShowLayout, TextField, NumberField, BooleanField } from 'react-admin';
import { ProtectedShowActions } from "../PermissionGuards";

export const OrigineShow = () => (
    <Show actions={<ProtectedShowActions />}>
        <SimpleShowLayout>
            <TextField source="name" label="Nom"/>
            <NumberField source="discount" label="Remise" options={{ style: 'percent' }}/>
            <BooleanField source="hasCommission" label="Rétro-commission"/>
        </SimpleShowLayout>
    </Show>
)