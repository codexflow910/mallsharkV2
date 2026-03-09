
//<!--Time Required for shifts, attendance & shiftreports-->
var timezone_offset_minutes = new Date().getTimezoneOffset();
timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;

// Timezone difference in minutes such as 330 or -360 or 0
console.log(timezone_offset_minutes); 




deleteCookie('clientTimeZone');
setCookie('clientTimeZone', timezone_offset_minutes);

function setCookie(name, value) {
    document.cookie = name + '=' + value + '; Path=/;';
}
function deleteCookie(name) {
    document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}