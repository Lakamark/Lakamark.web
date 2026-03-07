import { fetchApi, type FetchApiResult } from "./fetchApi";

interface VerifyCaptchaPayload {
    type: string;
    answer: string;
    challenge?: string | null;
}

export interface VerifyCaptchaResponse {
    valid?: boolean;
    locked?: boolean;
    error?: string;
}

/**
 * Verify a captcha answer against the API.
 */
export async function verifyCaptcha(
    payload: VerifyCaptchaPayload
): Promise<FetchApiResult<VerifyCaptchaResponse> | null> {
    return fetchApi<VerifyCaptchaResponse>("/captcha/verify", {
        method: "POST",
        body: JSON.stringify(payload),
    });
}