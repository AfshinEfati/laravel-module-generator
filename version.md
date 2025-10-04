// version 7.1.1 summary
// - Controllers using the action layer now reuse route-bound models (->getKey()) before delegating, avoiding duplicate queries【F:src/Stubs/Module/Controller/api-actions.stub†L17-L49】【F:src/Stubs/Module/Controller/web-actions.stub†L17-L63】
// - BaseAction logs the full exception context for better observability【F:src/Stubs/Module/Action/base.stub†L16-L33】
// - Docs cover --actions/--no-actions usage with fresh code samples
