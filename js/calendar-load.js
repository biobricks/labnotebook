function htmlDecode(input){
    var e = document.createElement('div')
    e.innerHTML = input
    return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue
}

function lnCalendarLoad() {

    // this will be the list of DOM elements containing proto-calendar children
    // i.e. the hidden divs written by LNCalendar.php
    var cals = []

    // LNCalendar.php wrote some divs inside the <p> tags.
    // Bring them out so they can actually be elements
    // turn <td><p>&lt;!-- into <td><!--
    var ps = document.getElementsByTagName("p")
    var n = 0
    for (var p in ps) {
        if (ps[n] && ps[n].innerHTML && /^&lt;!-- sibboleth/.test(ps[n].innerHTML)) {
            var heldhtml = ps[n].innerHTML
            cals.push({container:ps[n].parentElement})
            var ptd = ps[n].parentElement
            ptd.innerHTML = ''
            ptd.innerHTML = htmlDecode(heldhtml)
        }
        n++
    }

    // the year page has multiple calendars
    // not inside <p> but still displayed literally, need to be decoded
    // turn <td>&lt;!-- into <td><!--
    var tds = document.getElementsByTagName("td")
    var n = 0
    for (var td in tds) {
        if (tds[n] && tds[n].innerHTML && /^&lt;!-- sibboleth/.test(tds[n].innerHTML)) {
            var heldhtml = tds[n].innerHTML
            cals.push({container:tds[n]})
            var ptd = tds[n]
            tds[n].innerHTML = ''
            tds[n].innerHTML = htmlDecode(heldhtml)
        }
        n++
    }

    // for each calendar on the page
    for (var k=0; k < cals.length; k++) {
        var c = cals[k].container

        // use those hidden divs to assign vars
        cals[k].opts = []
        for (var i=0; i < c.children.length; i++) {
            if ((c.children[i].tagName.toLowerCase() == 'div') && (c.children[i].id.match(/^(lncal1|y[0-9]+)$/))) {
                for (var j=0; j < c.children[i].children.length; j++) {
                    var hiddendiv = c.children[i].children[j]
                    if (hiddendiv.tagName.toLowerCase() == 'div') {
                        cals[k].opts[hiddendiv.id] = hiddendiv.innerHTML
                    }
                }
            }
        }
        cals[k].popup = new CalendarPopup(cals[k].opts['id'])
        cals[k].popup.setDateFormat(cals[k].opts['fmt'])
        cals[k].popup.setCssPrefix(cals[k].opts['css'])
        if (cals[k].opts['month']) cals[k].popup.setSingleMonth(cals[k].opts['month'])
        if (cals[k].opts['year']) cals[k].popup.setYear(cals[k].opts['year'])
        cals[k].popup.setReadOnly(cals[k].opts['readonly'])
        cals[k].popup.setTodayText('')
        cals[k].popup.setUrlPrefix('/wiki/Special:Redir/'+cals[k].opts['page'])
        var dates = cals[k].opts['dtext'].split(',')
        for (var i=0; i<dates.length; i++) {
            if (dates[i]) cals[k].popup.addFilledDates(dates[i])
        }

        // now show calendar
        cals[k].popup.showCalendar(cals[k].opts['id'])
    }
}

function CalendarPageConfirmCreate(date, url) {
    if (confirm("Create entry for " + date + "?")) {
        window.location = url
    }
}

window.htmlDecode = htmlDecode
window.lnCalendarLoad = lnCalendarLoad
window.CalendarPageConfirmCreate = CalendarPageConfirmCreate
window.CP_refreshCalendar = CP_refreshCalendar

jQuery(document).ready(function($) {
    lnCalendarLoad()
})
