# Deployment Safety Rules

- **CRITICAL**: When modifying deployment scripts (e.g., GitHub Actions FTP-Deploy-Action), ALWAYS ensure that live user data is excluded from clean-slate deployments.
- **Specific Exclusions for HimYog**: The files `enquiries_backup_*.csv`, `admin_config_*.json`, `content.json`, and the directory `uploads/**` must ALWAYS be excluded from server wiping or FTP clean-slate syncs. 
- **Untracked Data**: Do not overwrite or allow automated tools to wipe untracked local or production data files. Protect these data stores at all costs.
