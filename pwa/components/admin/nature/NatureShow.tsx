import { Show, SimpleShowLayout, TextField, BooleanField } from 'react-admin';

export const NatureShow = () => (
    <Show>
        <SimpleShowLayout>
            <TextField source="code" label="Code"/>
            <TextField source="label" label="Label"/>
            <BooleanField source="isParticularActivity" label="Activité Particulière (AP)" />
        </SimpleShowLayout>
    </Show>
)