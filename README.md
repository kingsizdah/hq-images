# Container images

CI builds for custom container images. Currently:

- **`paymenter/`** — custom Paymenter image: pinned upstream, `filament-jalali`, Persian (`fa`) translations, RTL layout, no bundled gateways/server extensions/extra languages.

Images are pushed to `ghcr.io/kingsizdah/hq-images`.

## Build

- Push a change under `paymenter/` to `main` → builds automatically.
- Or run the **Build Paymenter image** workflow manually via `workflow_dispatch`.

The cluster consumes these images through `charts/paymenter/values.yaml` in the `hq-cluster` repo; update the tag there and Flux does the rollout.
