---@alias kdtsk.Settings table<string, table<string, {
---    use_for: table<"LSP"|"STYLE"|"INSPECTION", boolean>, -- use the tool for the given purpose
---    lsp_settings?: table, -- provide settings for LSP
---  }>>
---@type kdtsk.Settings
vim.g.settings = {
  css = {
    prettried = { use_for = { STYLE = true } },
  },
  php = {
    -- Language Support
    phpactor = { use_for = { LSP = true } },
    intelephense = { use_for = { LSP = true, STYLE = false } },
    -- Style
    php_cs_fixer = { use_for = { STYLE = true } },
    -- Quality
    psalm = { use_for = { LSP = false, INSPECTION = true } },
    phpstan = { use_for = { INSPECTION = true } },
  },
  json = {
    jsonls = { use_for = { LSP = true, STYLE = true } },
  },
}
vim.api.nvim_command("doautocmd User SettingsLoaded")

