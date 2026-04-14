import { Edit } from "react-admin";
import { IntegrationPatternForm } from "./IntegrationPatternForm";

export const IntegrationPatternEdit = () => (
  <Edit mutationMode="pessimistic">
    <IntegrationPatternForm />
  </Edit>
);
