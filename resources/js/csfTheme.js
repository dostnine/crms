// The light / dark theme of the CSF survey, as its form remembers it
// (localStorage "csf-theme"; dark until one is chosen). The pages a
// respondent goes through on the way to the form follow it, and the
// Citizen's Charter book sets it to its own mode when the survey is started
// from there.
export function csfTheme() {
    try {
        return localStorage.getItem('csf-theme') === 'light' ? 'light' : 'dark';
    } catch (e) {
        return 'dark';
    }
}
