import { Show, SimpleShowLayout, TextField, BooleanField } from 'react-admin';
import { ProtectedShowActions } from "../PermissionGuards";

export const NatureShow = () => (
    <Show actions={<ProtectedShowActions />}>
        <SimpleShowLayout>
            <TextField source="code" label="Code"/>
            <TextField source="label" label="Label"/>
            <BooleanField source="isParticularActivity" label="Activité Particulière (AP)" />
        </SimpleShowLayout>
    </Show>
)