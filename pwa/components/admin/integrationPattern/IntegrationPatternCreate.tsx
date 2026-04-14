import { Create } from "react-admin";
import { IntegrationPatternForm } from "./IntegrationPatternForm";

export const IntegrationPatternCreate = () => (
  <Create>
    <IntegrationPatternForm
      defaultValues={{
        method: "GET",
        contentType: "application/json",
        active: true,
      }}
    />
  </Create>
);
