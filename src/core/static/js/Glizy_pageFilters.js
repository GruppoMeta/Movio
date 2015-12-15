/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 * 
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */
 
var $F = function(el)
{
    return Glizy.$(el).val();
}

Glizy.pageFilters.draw = function()
{
	Glizy.pageFilters.rows = 1;
	Glizy.pageFilters.aCalendarAttach = [];
	aHTML = [];

	aHTML.push('<input type="hidden" id="'+Glizy.pageFilters.id+'_'+'count" name="'+Glizy.pageFilters.id+'_'+'count" value="'+name+'" />');
	aHTML.push('<table>');
	aHTML.push('<tr><td>');
	aHTML.push("<img onclick=\"Glizy.pageFilters.addRow(this)\" title=\""+pageTexts.addBefore+"\" src=\""+Glizy.pageFilters.iconPath+"icon_add.gif\" width=\"16\" height=\"16\" border=\"0\" style=\"cursor: pointer;\"/>");
	// aHTML.push('</td>');
	// aHTML.push('<td>');
	Glizy.pageFilters.drawItems(aHTML, Glizy.pageFilters.id+'_0', '');
	// aHTML.push('</td><td></td></tr>');
	aHTML.push('<div></div></td></tr>');

	// disegna i campi già impostati
	 for (var i=0; i<Glizy.pageFilters.values.values.length; i++)
	{
		aHTML.push('<tr><td>');
		aHTML.push("<img onclick=\"Glizy.pageFilters.removeRow(this)\" title=\""+pageTexts.del+"\" src=\""+Glizy.pageFilters.iconPath+"icon_delete.gif\" width=\"16\" height=\"16\" border=\"0\" style=\"cursor: pointer;\"/>");			
		// aHTML.push('</td>');
		// aHTML.push('<td>');
		Glizy.pageFilters.drawItems(aHTML, Glizy.pageFilters.id+'_'+(i+1), 'items.'+Glizy.pageFilters.values.values[i][0]);
		aHTML.push('<div>');
		Glizy.pageFilters.drawFilterElement(aHTML, Glizy.pageFilters.id+'_'+(i+1), Glizy.pageFilters.values.values[i][0], Glizy.pageFilters.values.values[i][1]);
		aHTML.push('</div></td></tr>');
		Glizy.pageFilters.rows++;
	}
	aHTML.push('</table>');
	document.write(aHTML.join(''));
	Glizy.$(Glizy.pageFilters.id+'_'+'count').value = Glizy.pageFilters.rows-1;
}


Glizy.pageFilters.drawItems = function(aHTML, id, toSelect)
{
	aHTML.push('<select id="'+id+'" name="'+id+'" onchange="Glizy.pageFilters.changeType(this, event);">');
	aHTML.push('<option value="">-</option>');
    for (var i in Glizy.pageFilters.items)
	{
		el = Glizy.pageFilters.items[i];
		if (!el.id) continue;
        aHTML.push('<option value="'+el.id+'"'+(el.id==toSelect ? ' selected="selected"':'')+'>'+el.label+'</option>');
    }
	aHTML.push('</select>');
}

Glizy.pageFilters.drawFilterElement = function(aHTML, id, name, value)
{
	name = name.indexOf('.')>1 ? name.split('.')[1] : name;
	fObj = Glizy.pageFilters.items[name];
	if (fObj)
	{
		if(!value) value = '';
		newId = id.replace(Glizy.pageFilters.id+'_', Glizy.pageFilters.id+'_value_');
		switch (fObj.type)
		{
			case 'select':
			case 'editableSelect':
				aHTML.push('<select id="'+newId+'" name="'+newId+'">');
				for (var i=0; i<fObj.options.length;i++)
				{
					aHTML.push('<option value="'+fObj.options[i][1]+'"'+(fObj.options[i][1]==value ? ' selected="selected"':'')+'>'+fObj.options[i][0]+'</option>');
				}
				aHTML.push('</select>');
				break;
			case 'checkbox':
				aHTML.push('<input type="hidden" id="'+newId+'" name="'+newId+'" value="'+(value=="1" ? '1' : '0' )+'" />')
				aHTML.push('<input onchange="Glizy.pageFilters.setCheckValue(this, \''+newId+'\')" value="1" type="checkbox"'+(value=="1"?' checked':'')+'/>')
				break;
			case 'date':
				aHTML.push('<input type="text" id="'+newId+'" name="'+newId+'" value="'+value+'" autocomplete="off" size="10" />');
				aHTML.push('<img src="'+Glizy.pageFilters.iconPath+'calendar.gif" style="cursor: pointer; border: 1px solid red;" title="Date selector" onmouseover="this.style.background=\'red\';" onmouseout="this.style.background=\'\'" id="'+newId+'bt"/>');
				Glizy.pageFilters.aCalendarAttach.push(newId);
				break;
			default:
				aHTML.push('<input type="text" id="'+newId+'" name="'+newId+'" value="'+value+'" />');
		}
		
		newId = id.replace(Glizy.pageFilters.id+'_', Glizy.pageFilters.id+'_type_');
		aHTML.push('<input type="hidden" id="'+newId+'" name="'+newId+'" value="'+name+'" />');
	}
}

Glizy.pageFilters.addRow = function(el)
{
	tEl = el.parentNode.parentNode.parentNode;
	row = tEl.insertRow(-1);
	Glizy.$(Glizy.pageFilters.id+'_'+'count').value = Glizy.pageFilters.rows;
	
	cell = row.insertCell(-1);
	aHTML = [];
	aHTML.push( "<img onclick=\"Glizy.pageFilters.removeRow(this)\" title=\""+pageTexts.del+"\" src=\""+Glizy.pageFilters.iconPath+"icon_delete.gif\" width=\"16\" height=\"16\" border=\"0\" style=\"cursor: pointer;\"/>" );
	Glizy.pageFilters.drawItems(aHTML, Glizy.pageFilters.id+'_'+Glizy.pageFilters.rows, '');
	cell.innerHTML = aHTML.join('');;
	cell = row.insertCell(-1);
	
	Glizy.pageFilters.rows++;
	Glizy.pageFilters.initCalendar();
}

Glizy.pageFilters.removeRow = function(el, rowNum)
{
	rowEl = el.parentNode.parentNode;
	rowEl.toDelete = true;
	tEl = el.parentNode.parentNode.parentNode;
	
	for (var i=0; i<tEl.rows.length;i++)
	{
		if (tEl.rows[i].toDelete==true)
		{
			tEl.deleteRow(i);
			break;
		}
	}
}

Glizy.pageFilters.changeType = function(el, event)
{
	aHTML = [];
	Glizy.pageFilters.drawFilterElement(aHTML, el.id, $F(el));
	var cell = el.nextSibling;
	cell.innerHTML = aHTML.join('');
	Glizy.pageFilters.initCalendar();
}

Glizy.pageFilters.setCheckValue = function(el, targetId)
{
	Glizy.$(targetId).value = el.checked ? '1' : '0';
}

Glizy.pageFilters.initCalendar = function()
{
	while (true) {
		var id = Glizy.pageFilters.aCalendarAttach.pop();
		el = Glizy.$(id);
		if (id && el) {
			Calendar.setup({
				inputField     :    id,      			// id of the input field
				ifFormat       :    GlizyLocale.DATE_FORMAT,       	// format of the input field
				showsTime      :    false,            	// will display a time selector
				button         :    id+"bt",   			// trigger for the calendar (button ID)
				singleClick    :    true,          		// double-click mode
				step           :    1,               	// show all years in drop-down boxes (instead of every other year as default)
				firstDay		:	1,
				weekNumbers		:	false,
				date			:	"",
				showOthers		:	true
			});
		}
		else break;
	}
}

Glizy.pageFilters.draw();
Glizy.pageFilters.initCalendar();