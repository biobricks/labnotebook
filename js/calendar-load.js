function htmlDecode(input){
    var e = document.createElement('div')
    e.innerHTML = input
    return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue
}

function lnCalendarLoad() {

    // LNCalendar.php wrote some divs inside the <p> tags.
    // Bring them out so they can actually be elements
    var ps = document.getElementsByTagName("p")
    var n = 0
    for (var p in ps) {
        if (ps[n] && ps[n].innerHTML && /^&lt;!-- sibboleth/.test(ps[n].innerHTML)) {
            var heldhtml = ps[n].innerHTML
            var td = ps[n].parentElement
            td.innerHTML = ''
            td.innerHTML = htmlDecode(heldhtml)
            break
        }
        n++
    }

    // use those hidden divs to assign vars
    var opts = []
    for (var i=0; i < td.children.length; i++) {
        if ((td.children[i].tagName.toLowerCase() == 'div') && (td.children[i].id == 'lncal1')) {
            for (var j=0; j < td.children[i].children.length; j++) {
                var hiddendiv = td.children[i].children[j]
                if (hiddendiv.tagName.toLowerCase() == 'div') {
                    opts[hiddendiv.id] = hiddendiv.innerHTML
                }
            }
        }
    }
    var cal = new CalendarPopup(opts['id'])
    cal.setDateFormat(opts['fmt'])
    cal.setCssPrefix(opts['css'])
    if (opts['month']) cal.setSingleMonth(opts['month'])
    if (opts['year']) cal.setYear(opts['year'])
    cal.setReadOnly(opts['readonly'])
    cal.setTodayText('')
    cal.setUrlPrefix('/wiki/Special:Redir/'+opts['page'])
    var dates = opts['dtext'].split(',')
    for (var i=0; i<dates.length; i++) {
        if (dates[i]) cal.addFilledDates(dates[i])
    }

    // now show calendar
    cal.showCalendar(opts['id'])
}

function CalendarPageConfirmCreate(date, url) {
    if (confirm("Create entry for " + date + "?")) {
        window.location = url
    }
}

jQuery(document).ready(function($) {
    lnCalendarLoad()
})
