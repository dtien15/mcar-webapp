<?php
// =====================================================================
// CaiDatModel - Cai dat he thong dung chung (luu trong bang app_settings),
// truoc mat dung cho cau hinh AI (OpenAI).
// =====================================================================

class CaiDatModel extends Model
{
    /** Doc 1 cai dat he thong theo ten */
    public function layCaiDat($ten)
    {
        $giaTri = $this->motGiaTri("SELECT value FROM app_settings WHERE name = ?", [$ten]);
        return $giaTri === false ? null : $giaTri;
    }

    /** Ghi 1 cai dat he thong */
    public function luuCaiDat($ten, $giaTri)
    {
        return $this->thucThi(
            "INSERT INTO app_settings (name, value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value)",
            [$ten, $giaTri]
        );
    }

    /** API key OpenAI dang luu (rong neu chua cau hinh) */
    public function layOpenAiApiKey()
    {
        return $this->layCaiDat('openai_api_key') ?: '';
    }

    /** Model OpenAI dang chon, mac dinh gpt-4o-mini neu chua cau hinh */
    public function layOpenAiModel()
    {
        return $this->layCaiDat('openai_model') ?: 'gpt-4o-mini';
    }
}
